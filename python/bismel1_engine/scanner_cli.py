# اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
# Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
# version: x
# ======================================================
# - App Name: gusgraph-trading
# - Gusgraph LLC -
# - Author: Gus Kazem
# - https://Gusgraph.com
# - File Path: python/bismel1_engine/scanner_cli.py
# ======================================================

from __future__ import annotations

import json
import sys
from collections import defaultdict
from datetime import UTC, datetime
from zoneinfo import ZoneInfo

from .models import Bismel1Config, NormalizedBar, PositionState, StrategyContext
from .strategy import evaluate_bismel1_strategy


def main() -> int:
    payload = json.load(sys.stdin)
    symbol = str(payload.get("symbol", "")).upper()
    config = Bismel1Config(**payload.get("config", {}))
    execution_bars = [_bar_from_mapping(item) for item in payload.get("bars_4h", [])]
    hourly_bars = [_bar_from_mapping(item) for item in payload.get("bars_1h", [])]
    trend_bars = _aggregate_daily_bars(hourly_bars, config.exchange_timezone)

    position_input = payload.get("position", {})
    position = PositionState(
        quantity=float(position_input.get("quantity", 0.0) or 0.0),
        average_price=float(position_input.get("average_price", 0.0) or 0.0),
        add_count=int(position_input.get("add_count", 0) or 0),
        last_add_price=float(position_input["last_add_price"]) if position_input.get("last_add_price") is not None else None,
        dollars_used=float(position_input.get("dollars_used", 0.0) or 0.0),
        pos_high=float(position_input["pos_high"]) if position_input.get("pos_high") is not None else None,
    )

    result = evaluate_bismel1_strategy(
        StrategyContext(
            execution_bars=execution_bars,
            trend_bars=trend_bars,
            position=position,
            strategy_equity=float(payload.get("strategy_equity", 10000.0) or 10000.0),
            symbol=symbol,
        ),
        config,
    )

    action = "skip"
    if result.action == "base_entry_candidate":
        action = "open"
    elif result.action == "recovery_add_candidate":
        action = "add"
    elif result.action == "exit_candidate":
        action = "close"

    unresolved_gaps = []
    if len(trend_bars) < config.ema_slow_length:
        unresolved_gaps.append("insufficient_daily_history_for_htf_ema_slow")
    if len(execution_bars) < config.ema_slow_length:
        unresolved_gaps.append("insufficient_4h_history_for_exec_ema_slow")
    if len(execution_bars) < config.swing_length:
        unresolved_gaps.append("insufficient_4h_history_for_pullback_window")

    output = {
        "symbol": symbol,
        "action": action,
        "raw_action": result.action,
        "safe_flags": {
            "trend_aligned": result.current.trend_ok,
            "pullback_detected": result.current.in_pullback_zone,
            "reclaim_confirmed": result.current.momentum_confirm,
            "risk_blocked": result.current.pause_new_basket or result.current.pause_adds or result.current.regime_fail,
            "trailing_exit": bool(result.exit_candidate and result.trail_stop is not None and result.current.bar.close <= result.trail_stop),
            "regime_fail": result.current.regime_fail,
        },
        "internal_strategy_state": {
            "raw_reason": result.reason,
            "raw_action": result.action,
            "position_state": {
                "quantity": result.updated_position.quantity,
                "average_price": result.updated_position.average_price,
                "add_count": result.updated_position.add_count,
                "last_add_price": result.updated_position.last_add_price,
                "dollars_used": result.updated_position.dollars_used,
                "pos_high": result.updated_position.pos_high,
            },
            "current": {
                "bar_close_time": _iso(result.current.bar.close_time),
                "atr_pct": result.current.atr_pct,
                "trend_ok": result.current.trend_ok,
                "in_pullback_zone": result.current.in_pullback_zone,
                "momentum_confirm": result.current.momentum_confirm,
                "regime_fail": result.current.regime_fail,
                "pause_new_basket": result.current.pause_new_basket,
                "pause_adds": result.current.pause_adds,
                "is_low_tier": result.current.is_low_tier,
            },
            "previous_bar_close_time": _iso(result.previous.bar.close_time) if result.previous else None,
            "order_notional": result.order_notional,
            "order_quantity": result.order_quantity,
            "add_step": result.add_step,
            "trail_stop": result.trail_stop,
            "unresolved_gaps": unresolved_gaps,
        },
    }

    json.dump(output, sys.stdout)
    sys.stdout.write("\n")
    return 0


def _bar_from_mapping(payload: dict[str, object]) -> NormalizedBar:
    starts_at = _parse_datetime(str(payload["starts_at"]))
    ends_at = _parse_datetime(str(payload["ends_at"])) if payload.get("ends_at") else None

    return NormalizedBar(
        starts_at=starts_at,
        ends_at=ends_at,
        open=float(payload["open"]),
        high=float(payload["high"]),
        low=float(payload["low"]),
        close=float(payload["close"]),
        volume=float(payload["volume"]) if payload.get("volume") is not None else None,
    )


def _aggregate_daily_bars(hourly_bars: list[NormalizedBar], exchange_timezone: str) -> list[NormalizedBar]:
    timezone = ZoneInfo(exchange_timezone)
    grouped: dict[datetime.date, list[NormalizedBar]] = defaultdict(list)

    for bar in sorted(hourly_bars, key=lambda item: item.close_time):
        grouped[bar.close_time.astimezone(timezone).date()].append(bar)

    daily_bars: list[NormalizedBar] = []
    for group in grouped.values():
        ordered = sorted(group, key=lambda item: item.close_time)
        first = ordered[0]
        last = ordered[-1]
        daily_bars.append(
            NormalizedBar(
                starts_at=first.starts_at,
                ends_at=last.close_time,
                open=first.open,
                high=max(item.high for item in ordered),
                low=min(item.low for item in ordered),
                close=last.close,
                volume=sum(item.volume or 0.0 for item in ordered) or None,
            )
        )

    return sorted(daily_bars, key=lambda item: item.close_time)


def _parse_datetime(value: str) -> datetime:
    normalized = value.replace("Z", "+00:00")
    parsed = datetime.fromisoformat(normalized)
    return parsed if parsed.tzinfo is not None else parsed.replace(tzinfo=UTC)


def _iso(value: datetime | None) -> str | None:
    return value.astimezone(UTC).isoformat().replace("+00:00", "Z") if value else None


if __name__ == "__main__":
    raise SystemExit(main())
