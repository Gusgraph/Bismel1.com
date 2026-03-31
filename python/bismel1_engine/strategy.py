# اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
# Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
# version: x
# ======================================================
# - App Name: gusgraph-trading
# - Gusgraph LLC -
# - Author: Gus Kazem
# - https://Gusgraph.com
# - File Path: python/bismel1_engine/strategy.py
# ======================================================

from __future__ import annotations

from datetime import datetime, time
from zoneinfo import ZoneInfo

from .indicators import atr, ema, rolling_highest, rolling_lowest, rsi
from .models import Bismel1Config, EvaluationResult, IndicatorSnapshot, PositionState, StrategyContext


def build_indicator_history(
    execution_bars: list,
    trend_bars: list,
    config: Bismel1Config | None = None,
) -> list[IndicatorSnapshot]:
    config = config or Bismel1Config()
    exec_bars = sorted(execution_bars, key=lambda item: item.close_time)
    daily_bars = sorted(trend_bars, key=lambda item: item.close_time)

    exec_opens = [bar.open for bar in exec_bars]
    exec_highs = [bar.high for bar in exec_bars]
    exec_lows = [bar.low for bar in exec_bars]
    exec_closes = [bar.close for bar in exec_bars]

    exec_ema_fast = ema(exec_closes, config.ema_fast_length)
    exec_ema_slow = ema(exec_closes, config.ema_slow_length)
    exec_rsi = rsi(exec_closes, config.rsi_length)
    exec_atr = atr(exec_highs, exec_lows, exec_closes, config.atr_length)
    swing_highs = rolling_highest(exec_highs, config.swing_length)
    swing_lows = rolling_lowest(exec_lows, config.swing_length)
    reclaim_lows = rolling_lowest(exec_lows, config.price_reclaim_bars)

    daily_closes = [bar.close for bar in daily_bars]
    daily_ema_fast = ema(daily_closes, config.ema_fast_length)
    daily_ema_slow = ema(daily_closes, config.ema_slow_length)

    snapshots: list[IndicatorSnapshot] = []
    trend_index = -1
    timezone = ZoneInfo(config.exchange_timezone)
    session_start = _parse_session_time(config.trade_session_start)
    session_end = _parse_session_time(config.trade_session_end)

    for index, bar in enumerate(exec_bars):
        while trend_index + 1 < len(daily_bars) and daily_bars[trend_index + 1].close_time <= bar.close_time:
            trend_index += 1

        htf_close = None
        htf_ema_fast = None
        htf_ema_slow = None
        htf_ema_slow_prev = None
        htf_ema_slow_slope_up = False
        trend_base_htf = False
        trend_ok = False

        if trend_index >= 0:
            htf_close = daily_bars[trend_index].close
            htf_ema_fast = daily_ema_fast[trend_index]
            htf_ema_slow = daily_ema_slow[trend_index]
            previous_slope_index = trend_index - config.ema_slow_slope_lookback
            if previous_slope_index >= 0:
                htf_ema_slow_prev = daily_ema_slow[previous_slope_index]

            if htf_ema_slow is not None and htf_ema_slow_prev is not None:
                htf_ema_slow_slope_up = htf_ema_slow > htf_ema_slow_prev

            if htf_ema_fast is not None and htf_ema_slow is not None:
                trend_base_htf = (
                    htf_ema_fast > htf_ema_slow
                    and htf_close > htf_ema_slow
                    and htf_close > htf_ema_fast
                )
                trend_ok = trend_base_htf and (
                    htf_ema_slow_slope_up if config.require_ema_slow_slope_up else True
                )

        atr_value = exec_atr[index]
        atr_pct = ((atr_value / bar.close) * 100.0) if atr_value is not None and bar.close > 0 else None
        swing_high = swing_highs[index]
        swing_low = swing_lows[index]
        swing_range = (swing_high - swing_low) if swing_high is not None and swing_low is not None else None
        pullback_depth = None
        if swing_range is not None and swing_range > 0:
            pullback_depth = (swing_high - bar.close) / swing_range

        in_pullback_zone = pullback_depth is not None and pullback_depth >= config.pullback_min_depth

        rsi_cross_mode = False
        fast_reclaim_mode = False
        momentum_confirm = False
        previous_close = exec_closes[index - 1] if index > 0 else None
        previous_high = exec_highs[index - 1] if index > 0 else None
        previous_rsi = exec_rsi[index - 1] if index > 0 else None
        ema_fast_value = exec_ema_fast[index]
        reclaim_low = reclaim_lows[index]

        if previous_rsi is not None and exec_rsi[index] is not None and previous_high is not None:
            rsi_cross_mode = previous_rsi <= config.rsi_turn_up and exec_rsi[index] > config.rsi_turn_up and bar.close > previous_high

        fast_reclaim_mode = (
            previous_close is not None
            and ema_fast_value is not None
            and reclaim_low is not None
            and bar.close > bar.open
            and bar.close > previous_close
            and bar.close > ema_fast_value
            and reclaim_low <= ema_fast_value
        )

        if config.entry_mode == "RSI Crossover":
            momentum_confirm = rsi_cross_mode
        else:
            momentum_confirm = (
                exec_rsi[index] is not None
                and exec_rsi[index] > config.rsi_turn_up
                and fast_reclaim_mode
            )

        in_session = _bar_in_session(bar.starts_at, bar.close_time, timezone, session_start, session_end)
        is_weekday = bar.close_time.astimezone(timezone).weekday() < 5
        session_ok = (
            in_session and (is_weekday if config.trade_weekdays_only else True)
            if config.use_session_filter
            else True
        )

        regime_fail = not trend_ok
        auto_paused = config.use_auto_pause and config.auto_pause_on_regime_fail and regime_fail
        pause_new_basket = config.pause_new_entries_manual or (
            config.pause_on_regime_fail and auto_paused
        ) or (not session_ok)
        pause_adds = config.pause_new_entries_manual or (not session_ok)
        is_low_tier = atr_pct is not None and atr_pct < config.atr_pct_tier_threshold

        snapshots.append(
            IndicatorSnapshot(
                bar=bar,
                ema_fast_exec=ema_fast_value,
                ema_slow_exec=exec_ema_slow[index],
                rsi=exec_rsi[index],
                atr=atr_value,
                atr_pct=atr_pct,
                swing_high=swing_high,
                swing_low=swing_low,
                pullback_depth=pullback_depth,
                in_pullback_zone=in_pullback_zone,
                lowest_low_reclaim=reclaim_low,
                rsi_cross_mode=rsi_cross_mode,
                fast_reclaim_mode=fast_reclaim_mode,
                momentum_confirm=momentum_confirm,
                htf_close=htf_close,
                htf_ema_fast=htf_ema_fast,
                htf_ema_slow=htf_ema_slow,
                htf_ema_slow_prev=htf_ema_slow_prev,
                htf_ema_slow_slope_up=htf_ema_slow_slope_up,
                trend_base_htf=trend_base_htf,
                trend_ok=trend_ok,
                in_session=in_session,
                is_weekday=is_weekday,
                session_ok=session_ok,
                regime_fail=regime_fail,
                auto_paused=auto_paused,
                pause_new_basket=pause_new_basket,
                pause_adds=pause_adds,
                is_low_tier=is_low_tier,
            )
        )

    return snapshots


def evaluate_bismel1_strategy(
    context: StrategyContext,
    config: Bismel1Config | None = None,
) -> EvaluationResult:
    config = config or Bismel1Config()
    snapshots = build_indicator_history(context.execution_bars, context.trend_bars, config)
    if not snapshots:
        raise ValueError("At least one execution bar is required.")

    current = snapshots[-1]
    previous = snapshots[-2] if len(snapshots) > 1 else None
    position = context.position

    cap_now = context.strategy_equity * (config.max_basket_pct_equity / 100.0)
    current_close = current.bar.close
    updated_pos_high = max(position.pos_high or current.bar.high, current.bar.high) if position.in_position else None
    trail_stop = (
        updated_pos_high - (current.atr * config.atr_trail_mult)
        if position.in_position and updated_pos_high is not None and current.atr is not None
        else None
    )

    base_signal = _base_entry_signal(current, config)
    previous_base_signal = _base_entry_signal(previous, config) if previous else False
    first_dollars = config.dollars_for_step(0)
    first_quantity = (first_dollars / current_close) if current_close > 0 else 0.0
    base_entry_candidate = (
        not position.in_position
        and base_signal
        and not previous_base_signal
        and first_dollars <= cap_now
        and first_quantity > 0
        and config.asset_class == "equity"
    )

    add_step = position.add_count + 1
    step_dollars = config.dollars_for_step(add_step)
    step_quantity = (step_dollars / current_close) if current_close > 0 else 0.0
    add_signal = _recovery_add_signal(current, previous, position, config, cap_now, step_dollars)
    previous_add_signal = _recovery_add_signal(previous, snapshots[-3] if len(snapshots) > 2 else None, position, config, cap_now, step_dollars) if previous else False
    recovery_add_candidate = (
        position.in_position
        and add_step <= config.max_adds
        and add_signal
        and not previous_add_signal
        and step_quantity > 0
    )

    exit_candidate = position.in_position and (
        (trail_stop is not None and current.bar.close <= trail_stop)
        or (config.exit_on_regime_fail and current.regime_fail)
    )

    regime_fail_pause = (
        not exit_candidate
        and not base_entry_candidate
        and not recovery_add_candidate
        and (current.regime_fail or current.pause_new_basket or current.pause_adds)
    )

    updated_position = position
    action = "skip_no_action"
    reason = "No closed-candle Bismel1 signal fired."
    order_notional = None
    order_quantity = None

    if exit_candidate:
        action = "exit_candidate"
        reason = "Closed-candle exit triggered by ATR trail or regime fail."
        updated_position = PositionState()
    elif base_entry_candidate:
        action = "base_entry_candidate"
        reason = "Closed-candle base entry reclaimed the fast EMA inside a valid HTF regime."
        order_notional = first_dollars
        order_quantity = first_quantity
        updated_position = PositionState(
            quantity=first_quantity,
            average_price=current.bar.close,
            add_count=0,
            last_add_price=current.bar.close,
            dollars_used=first_dollars,
            pos_high=current.bar.high,
        )
    elif recovery_add_candidate:
        action = "recovery_add_candidate"
        reason = "Closed-candle recovery add passed bounce, ATR spacing, drawdown, and basket-cap gates."
        order_notional = step_dollars
        order_quantity = step_quantity
        updated_position = PositionState(
            quantity=position.quantity + step_quantity,
            average_price=position.average_price,
            add_count=position.add_count + 1,
            last_add_price=current.bar.close,
            dollars_used=position.dollars_used + step_dollars,
            pos_high=updated_pos_high,
        )
    elif regime_fail_pause:
        action = "regime_fail_pause"
        reason = "Closed-candle state is paused by regime or session gating."
        updated_position = PositionState(
            quantity=position.quantity,
            average_price=position.average_price,
            add_count=position.add_count,
            last_add_price=position.last_add_price,
            dollars_used=position.dollars_used,
            pos_high=updated_pos_high,
        )
    elif position.in_position:
        updated_position = PositionState(
            quantity=position.quantity,
            average_price=position.average_price,
            add_count=position.add_count,
            last_add_price=position.last_add_price,
            dollars_used=position.dollars_used,
            pos_high=updated_pos_high,
        )

    return EvaluationResult(
        action=action,
        reason=reason,
        current=current,
        previous=previous,
        base_entry_candidate=base_entry_candidate,
        recovery_add_candidate=recovery_add_candidate,
        exit_candidate=exit_candidate,
        regime_fail_pause=regime_fail_pause,
        skip_no_action=action == "skip_no_action",
        order_notional=order_notional,
        order_quantity=order_quantity,
        add_step=add_step if recovery_add_candidate else None,
        trail_stop=trail_stop,
        updated_position=updated_position,
    )


def _base_entry_signal(snapshot: IndicatorSnapshot | None, config: Bismel1Config) -> bool:
    if snapshot is None:
        return False
    return (
        config.asset_class == "equity"
        and (not snapshot.pause_new_basket)
        and snapshot.trend_ok
        and snapshot.in_pullback_zone
        and snapshot.momentum_confirm
    )


def _recovery_add_signal(
    current: IndicatorSnapshot | None,
    previous: IndicatorSnapshot | None,
    position: PositionState,
    config: Bismel1Config,
    cap_now: float,
    step_dollars: float,
) -> bool:
    if current is None or previous is None or not position.in_position or position.add_count >= config.max_adds:
        return False

    if current.pause_adds or not current.in_pullback_zone or current.rsi is None or current.atr is None:
        return False

    step = position.add_count + 1
    add_bounce_confirm = (
        current.bar.close > current.bar.open
        and current.bar.close > previous.bar.close
        and current.rsi > max(20.0, config.rsi_turn_up - 8.0)
    )

    need_atr = _spacing_atr(step, current.is_low_tier)
    need_drop_pct = _min_drop_pct(step, current.is_low_tier)
    gate_atr_ok = (
        position.last_add_price is not None
        and current.bar.close <= (position.last_add_price - (current.atr * need_atr))
    )
    gate_drop_ok = (
        position.average_price > 0
        and current.bar.close <= (position.average_price * (1.0 - (need_drop_pct / 100.0)))
    )
    cap_ok = (position.dollars_used + step_dollars) <= (cap_now + 1e-10)

    return add_bounce_confirm and gate_atr_ok and gate_drop_ok and cap_ok


def _spacing_atr(step: int, is_low_tier: bool) -> float:
    if is_low_tier:
        if step == 1:
            return 1.5
        if step == 2:
            return 1.8
        if step == 3:
            return 2.2
        return 2.8

    if step == 1:
        return 3.0
    if step == 2:
        return 3.0
    if step == 3:
        return 4.0
    return 7.0


def _min_drop_pct(step: int, is_low_tier: bool) -> float:
    if is_low_tier:
        if step == 1:
            return 0.80
        if step == 2:
            return 1.30
        if step == 3:
            return 1.90
        return 2.60

    if step == 1:
        return 2.80
    if step == 2:
        return 3.30
    if step == 3:
        return 7.90
    return 9.60


def _parse_session_time(value: str) -> time:
    hour, minute = value.split(":", 1)
    return time(hour=int(hour), minute=int(minute))


def _bar_in_session(
    starts_at: datetime,
    ends_at: datetime,
    timezone: ZoneInfo,
    session_start: time,
    session_end: time,
) -> bool:
    local_start = starts_at.astimezone(timezone)
    local_end = ends_at.astimezone(timezone)
    session_open = datetime.combine(local_end.date(), session_start, timezone)
    session_close = datetime.combine(local_end.date(), session_end, timezone)

    return max(local_start, session_open) < min(local_end, session_close)
