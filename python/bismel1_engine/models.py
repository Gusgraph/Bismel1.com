# اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
# Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
# version: x
# ======================================================
# - App Name: gusgraph-trading
# - Gusgraph LLC -
# - Author: Gus Kazem
# - https://Gusgraph.com
# - File Path: python/bismel1_engine/models.py
# ======================================================

from __future__ import annotations

from dataclasses import dataclass
from datetime import datetime
from typing import Mapping


@dataclass(frozen=True)
class NormalizedBar:
    starts_at: datetime
    open: float
    high: float
    low: float
    close: float
    volume: float | None = None
    ends_at: datetime | None = None

    @property
    def close_time(self) -> datetime:
        return self.ends_at or self.starts_at

    @classmethod
    def from_mapping(cls, payload: Mapping[str, object]) -> "NormalizedBar":
        return cls(
            starts_at=payload["starts_at"],  # type: ignore[arg-type]
            ends_at=payload.get("ends_at"),  # type: ignore[arg-type]
            open=float(payload["open"]),
            high=float(payload["high"]),
            low=float(payload["low"]),
            close=float(payload["close"]),
            volume=float(payload["volume"]) if payload.get("volume") is not None else None,
        )


@dataclass(frozen=True)
class Bismel1Config:
    ema_fast_length: int = 50
    ema_slow_length: int = 200
    swing_length: int = 20
    pullback_min_depth: float = 0.40
    rsi_length: int = 14
    atr_length: int = 14
    entry_mode: str = "Fast Reclaim"
    price_reclaim_bars: int = 2
    rsi_turn_up: float = 46.0
    max_adds: int = 2
    first_lot_dollars: float = 100.0
    q1: float = 1.2
    q2: float = 1.6
    q3: float = 2.0
    q4: float = 2.5
    max_basket_pct_equity: float = 10.0
    atr_trail_mult: float = 3.0
    exit_on_regime_fail: bool = True
    pause_new_entries_manual: bool = False
    pause_on_regime_fail: bool = True
    use_auto_pause: bool = True
    auto_pause_on_regime_fail: bool = True
    require_ema_slow_slope_up: bool = True
    ema_slow_slope_lookback: int = 10
    use_session_filter: bool = False
    trade_session_start: str = "09:30"
    trade_session_end: str = "16:00"
    trade_weekdays_only: bool = True
    exchange_timezone: str = "America/New_York"
    asset_class: str = "equity"
    atr_pct_tier_threshold: float = 1.2

    def qty_multiplier(self, step: int) -> float:
        if step <= 0:
            return 1.0
        if step == 1:
            return self.q1
        if step == 2:
            return self.q2
        if step == 3:
            return self.q3
        return self.q4

    def dollars_for_step(self, step: int) -> float:
        return self.first_lot_dollars * self.qty_multiplier(step)


@dataclass(frozen=True)
class PositionState:
    quantity: float = 0.0
    average_price: float = 0.0
    add_count: int = 0
    last_add_price: float | None = None
    dollars_used: float = 0.0
    pos_high: float | None = None

    @property
    def in_position(self) -> bool:
        return self.quantity > 0


@dataclass(frozen=True)
class StrategyContext:
    execution_bars: list[NormalizedBar]
    trend_bars: list[NormalizedBar]
    position: PositionState = PositionState()
    strategy_equity: float = 10000.0
    symbol: str = ""


@dataclass(frozen=True)
class IndicatorSnapshot:
    bar: NormalizedBar
    ema_fast_exec: float | None
    ema_slow_exec: float | None
    rsi: float | None
    atr: float | None
    atr_pct: float | None
    swing_high: float | None
    swing_low: float | None
    pullback_depth: float | None
    in_pullback_zone: bool
    lowest_low_reclaim: float | None
    rsi_cross_mode: bool
    fast_reclaim_mode: bool
    momentum_confirm: bool
    htf_close: float | None
    htf_ema_fast: float | None
    htf_ema_slow: float | None
    htf_ema_slow_prev: float | None
    htf_ema_slow_slope_up: bool
    trend_base_htf: bool
    trend_ok: bool
    in_session: bool
    is_weekday: bool
    session_ok: bool
    regime_fail: bool
    auto_paused: bool
    pause_new_basket: bool
    pause_adds: bool
    is_low_tier: bool


@dataclass(frozen=True)
class EvaluationResult:
    action: str
    reason: str
    current: IndicatorSnapshot
    previous: IndicatorSnapshot | None
    base_entry_candidate: bool
    recovery_add_candidate: bool
    exit_candidate: bool
    regime_fail_pause: bool
    skip_no_action: bool
    order_notional: float | None = None
    order_quantity: float | None = None
    add_step: int | None = None
    trail_stop: float | None = None
    updated_position: PositionState = PositionState()
