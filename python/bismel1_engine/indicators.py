# اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
# Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
# version: x
# ======================================================
# - App Name: gusgraph-trading
# - Gusgraph LLC -
# - Author: Gus Kazem
# - https://Gusgraph.com
# - File Path: python/bismel1_engine/indicators.py
# ======================================================

from __future__ import annotations

from collections import deque


def ema(values: list[float], length: int) -> list[float | None]:
    result: list[float | None] = [None] * len(values)
    if length <= 0 or len(values) < length:
        return result

    seed = sum(values[:length]) / length
    result[length - 1] = seed
    alpha = 2.0 / (length + 1.0)

    previous = seed
    for index in range(length, len(values)):
        previous = (values[index] * alpha) + (previous * (1.0 - alpha))
        result[index] = previous

    return result


def rma(values: list[float], length: int) -> list[float | None]:
    result: list[float | None] = [None] * len(values)
    if length <= 0 or len(values) < length:
        return result

    seed = sum(values[:length]) / length
    result[length - 1] = seed
    previous = seed

    for index in range(length, len(values)):
        previous = ((previous * (length - 1)) + values[index]) / length
        result[index] = previous

    return result


def rsi(closes: list[float], length: int) -> list[float | None]:
    result: list[float | None] = [None] * len(closes)
    if length <= 0 or len(closes) <= length:
        return result

    gains: list[float] = []
    losses: list[float] = []

    for index in range(1, len(closes)):
        change = closes[index] - closes[index - 1]
        gains.append(max(change, 0.0))
        losses.append(max(-change, 0.0))

    avg_gain = sum(gains[:length]) / length
    avg_loss = sum(losses[:length]) / length

    first_index = length
    if avg_loss == 0.0:
        result[first_index] = 100.0
    else:
        rs = avg_gain / avg_loss
        result[first_index] = 100.0 - (100.0 / (1.0 + rs))

    for index in range(length + 1, len(closes)):
        gain = gains[index - 1]
        loss = losses[index - 1]
        avg_gain = ((avg_gain * (length - 1)) + gain) / length
        avg_loss = ((avg_loss * (length - 1)) + loss) / length

        if avg_loss == 0.0:
            result[index] = 100.0
        else:
            rs = avg_gain / avg_loss
            result[index] = 100.0 - (100.0 / (1.0 + rs))

    return result


def true_range(highs: list[float], lows: list[float], closes: list[float]) -> list[float]:
    result: list[float] = []
    previous_close: float | None = None

    for high, low, close in zip(highs, lows, closes):
        if previous_close is None:
            result.append(high - low)
        else:
            result.append(max(high - low, abs(high - previous_close), abs(low - previous_close)))
        previous_close = close

    return result


def atr(highs: list[float], lows: list[float], closes: list[float], length: int) -> list[float | None]:
    return rma(true_range(highs, lows, closes), length)


def rolling_highest(values: list[float], length: int) -> list[float | None]:
    result: list[float | None] = [None] * len(values)
    window: deque[float] = deque(maxlen=length)

    for index, value in enumerate(values):
        window.append(value)
        if len(window) == length:
            result[index] = max(window)

    return result


def rolling_lowest(values: list[float], length: int) -> list[float | None]:
    result: list[float | None] = [None] * len(values)
    window: deque[float] = deque(maxlen=length)

    for index, value in enumerate(values):
        window.append(value)
        if len(window) == length:
            result[index] = min(window)

    return result
