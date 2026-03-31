# اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
# Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
# version: x
# ======================================================
# - App Name: gusgraph-trading
# - Gusgraph LLC -
# - Author: Gus Kazem
# - https://Gusgraph.com
# - File Path: python/tests/test_bismel1_strategy.py
# ======================================================

from __future__ import annotations

import unittest
from datetime import UTC, datetime, timedelta

from python.bismel1_engine import Bismel1Config, NormalizedBar, PositionState, StrategyContext, evaluate_bismel1_strategy


def make_bar(ts: datetime, open_price: float, high: float, low: float, close: float, hours: int) -> NormalizedBar:
    return NormalizedBar(
        starts_at=ts,
        ends_at=ts + timedelta(hours=hours),
        open=open_price,
        high=high,
        low=low,
        close=close,
        volume=1000,
    )


class Bismel1StrategyTest(unittest.TestCase):
    def setUp(self) -> None:
        self.config = Bismel1Config(
            ema_fast_length=5,
            ema_slow_length=10,
            swing_length=5,
            rsi_length=5,
            atr_length=5,
            price_reclaim_bars=2,
            ema_slow_slope_lookback=3,
        )

    def test_base_entry_candidate(self) -> None:
        trend_bars = []
        daily_start = datetime(2026, 1, 1, tzinfo=UTC)
        for offset in range(30):
            price = 100 + offset
            trend_bars.append(make_bar(daily_start + timedelta(days=offset), price - 0.5, price + 1.0, price - 1.0, price, 24))

        execution_bars = []
        exec_start = datetime(2026, 2, 1, tzinfo=UTC)
        closes = [
            110, 111, 112, 113, 114, 115, 116, 117, 118, 119,
            120, 121, 122, 123, 124, 125, 126, 127, 128, 129,
            130, 131, 132, 133, 132, 131, 130, 129, 128.4, 130.7,
        ]

        for index, close in enumerate(closes):
            previous_close = closes[index - 1] if index > 0 else close - 0.5
            if index == len(closes) - 2:
                open_price = close + 0.6
            elif index == len(closes) - 1:
                open_price = close - 1.1
            else:
                open_price = previous_close - 0.2
            high = (136.0 if index == len(closes) - 1 else max(open_price, close) + 0.5)
            low = min(open_price, close) - (1.2 if index >= len(closes) - 2 else 0.4)
            execution_bars.append(make_bar(exec_start + timedelta(hours=4 * index), open_price, high, low, close, 4))

        result = evaluate_bismel1_strategy(
            StrategyContext(execution_bars=execution_bars, trend_bars=trend_bars, strategy_equity=10000.0),
            self.config,
        )

        self.assertEqual(result.action, "base_entry_candidate")
        self.assertTrue(result.base_entry_candidate)
        self.assertGreater(result.order_notional or 0.0, 0.0)

    def test_recovery_add_candidate(self) -> None:
        trend_bars = []
        daily_start = datetime(2026, 3, 1, tzinfo=UTC)
        for offset in range(25):
            price = 120 + offset
            trend_bars.append(make_bar(daily_start + timedelta(days=offset), price - 0.5, price + 1.0, price - 1.0, price, 24))

        execution_bars = []
        exec_start = datetime(2026, 3, 25, tzinfo=UTC)
        closes = [
            140, 141, 142, 143, 144, 145, 146, 147, 148, 149,
            150, 151, 152, 153, 154, 155, 156, 154.0, 150.0, 150.6,
        ]

        for index, close in enumerate(closes):
            previous_close = closes[index - 1] if index > 0 else close - 0.5
            if index == len(closes) - 2:
                open_price = close + 0.6
            elif index == len(closes) - 1:
                open_price = close - 0.6
            else:
                open_price = previous_close - 0.2
            high = max(open_price, close) + 0.2
            low = min(open_price, close) - 0.2
            execution_bars.append(make_bar(exec_start + timedelta(hours=4 * index), open_price, high, low, close, 4))

        result = evaluate_bismel1_strategy(
            StrategyContext(
                execution_bars=execution_bars,
                trend_bars=trend_bars,
                strategy_equity=10000.0,
                position=PositionState(
                    quantity=1.0,
                    average_price=155.0,
                    add_count=0,
                    last_add_price=157.0,
                    dollars_used=100.0,
                    pos_high=156.4,
                ),
            ),
            self.config,
        )

        self.assertEqual(result.action, "recovery_add_candidate")
        self.assertTrue(result.recovery_add_candidate)
        self.assertEqual(result.add_step, 1)

    def test_exit_candidate_from_atr_trail(self) -> None:
        trend_bars = []
        daily_start = datetime(2026, 4, 1, tzinfo=UTC)
        for offset in range(25):
            price = 140 + offset
            trend_bars.append(make_bar(daily_start + timedelta(days=offset), price - 0.5, price + 1.0, price - 1.0, price, 24))

        execution_bars = []
        exec_start = datetime(2026, 4, 25, tzinfo=UTC)
        closes = [160, 161, 162, 163, 164, 165, 166, 167, 166, 165, 164, 160]

        for index, close in enumerate(closes):
            previous_close = closes[index - 1] if index > 0 else close - 0.5
            open_price = previous_close - 0.1
            high = max(open_price, close) + 0.3
            low = min(open_price, close) - 0.7
            execution_bars.append(make_bar(exec_start + timedelta(hours=4 * index), open_price, high, low, close, 4))

        result = evaluate_bismel1_strategy(
            StrategyContext(
                execution_bars=execution_bars,
                trend_bars=trend_bars,
                strategy_equity=10000.0,
                position=PositionState(
                    quantity=1.0,
                    average_price=162.0,
                    add_count=1,
                    last_add_price=164.0,
                    dollars_used=220.0,
                    pos_high=168.0,
                ),
            ),
            self.config,
        )

        self.assertEqual(result.action, "exit_candidate")
        self.assertTrue(result.exit_candidate)


if __name__ == "__main__":
    unittest.main()
