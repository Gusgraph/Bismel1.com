// اعوز بالله من الشياطين و ان يحضرون بسم الله الرحمن الرحيم الله لا إله إلا هو الحي القيوم
// Bismillahi ar-Rahmani ar-Rahim Audhu billahi min ash-shayatin wa an yahdurun Bismillah ar-Rahman ar-Rahim Allah la ilaha illa huwa al-hayy al-qayyum. Tamsa Allahu ala ayunihim
// version: x
// ======================================================
// - App Name: Bismel1.com
// - Gusgraph LLC -
// - Author: Gus Kazem
// - https://Gusgraph.com
// - File Path: resources/js/market-background.js
// ======================================================

class MarketBackground {
    constructor(canvas, options = {}) {
        if (!canvas) {
            return;
        }

        this.canvas = canvas;
        this.ctx = this.canvas.getContext("2d", { alpha: true });

        this.options = {
            gridGap: 73,
            candleCount: 47,
            particleCount: 73,
            lineCount: 4,
            pulseChance: 0.027,
            speed: 0.73,
            lineSpeed: 1.11,
            particleSpeed: 0.27,
            opacity: 0.73,
            mobileScale: 0.56,
            ...options,
        };

        this.width = 0;
        this.height = 0;
        this.dpr = Math.min(window.devicePixelRatio || 1, 2);
        this.frameId = null;
        this.lastTime = 0;
        this.isReducedMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;
        this.pointer = {
            x: null,
            y: null,
            active: false,
        };

        this.gridLines = [];
        this.candles = [];
        this.particles = [];
        this.priceLines = [];
        this.pulses = [];
        this.signalLocks = [];

        this.palette = {
            bgGlow: "rgba(17, 43, 73, 0.19)",
            grid: "rgba(71, 117, 179, 0.09)",
            lineA: "rgba(67, 211, 255, 0.17)",
            lineB: "rgba(31, 163, 255, 0.13)",
            lineC: "rgba(73, 255, 197, 0.11)",
            candleUp: "rgba(73, 255, 197, 0.15)",
            candleDown: "rgba(255, 111, 145, 0.11)",
            wick: "rgba(143, 201, 255, 0.17)",
            particle: "rgba(127, 225, 255, 0.35)",
            pulse: "rgba(73, 255, 197, 0.19)",
            highlight: "rgba(127, 225, 255, 0.19)",
        };

        this.handleResize = this.handleResize.bind(this);
        this.handlePointerMove = this.handlePointerMove.bind(this);
        this.handlePointerLeave = this.handlePointerLeave.bind(this);
        this.animate = this.animate.bind(this);

        this.init();
    }

    init() {
        this.handleResize();
        window.addEventListener("resize", this.handleResize);
        window.addEventListener("pointermove", this.handlePointerMove);
        window.addEventListener("pointerleave", this.handlePointerLeave);

        if (!this.isReducedMotion) {
            this.frameId = window.requestAnimationFrame(this.animate);
        } else {
            this.renderStatic();
        }
    }

    destroy() {
        window.removeEventListener("resize", this.handleResize);
        window.removeEventListener("pointermove", this.handlePointerMove);
        window.removeEventListener("pointerleave", this.handlePointerLeave);

        if (this.frameId) {
            window.cancelAnimationFrame(this.frameId);
        }
    }

    handleResize() {
        const rect = this.canvas.getBoundingClientRect();

        this.width = Math.max(Math.floor(rect.width), 1);
        this.height = Math.max(Math.floor(rect.height), 1);

        this.canvas.width = Math.floor(this.width * this.dpr);
        this.canvas.height = Math.floor(this.height * this.dpr);

        this.ctx.setTransform(this.dpr, 0, 0, this.dpr, 0, 0);

        this.buildScene();
        this.renderStatic();
    }

    handlePointerMove(event) {
        const rect = this.canvas.getBoundingClientRect();

        this.pointer.x = event.clientX - rect.left;
        this.pointer.y = event.clientY - rect.top;
        this.pointer.active =
            this.pointer.x >= 0 &&
            this.pointer.x <= rect.width &&
            this.pointer.y >= 0 &&
            this.pointer.y <= rect.height;
    }

    handlePointerLeave() {
        this.pointer.x = null;
        this.pointer.y = null;
        this.pointer.active = false;
    }

    buildScene() {
        const isMobile = window.innerWidth < 767;
        const scale = isMobile ? this.options.mobileScale : 1;

        const candleCount = Math.max(11, Math.floor(this.options.candleCount * scale));
        const particleCount = Math.max(11, Math.floor(this.options.particleCount * scale));
        const lineCount = Math.max(1, Math.floor(this.options.lineCount));

        this.gridLines = this.createGridLines();
        this.candles = this.createCandles(candleCount);
        this.particles = this.createParticles(particleCount);
        this.priceLines = this.createPriceLines(lineCount);
        this.pulses = [];
        this.signalLocks = this.createSignalLocks(Math.max(3, Math.floor(lineCount + 2)));
    }

    createGridLines() {
        const lines = [];
        const gap = this.options.gridGap;

        for (let x = -gap; x <= this.width + gap; x += gap) {
            lines.push({
                type: "vertical",
                value: x,
            });
        }

        for (let y = -gap; y <= this.height + gap; y += gap) {
            lines.push({
                type: "horizontal",
                value: y,
            });
        }

        return lines;
    }

    createCandles(count) {
        const candles = [];

        for (let i = 0; i < count; i += 1) {
            const width = this.random(11, 23);
            const bodyHeight = this.random(27, 127);
            const wickTop = this.random(15, 57);
            const wickBottom = this.random(15, 57);
            const x = (this.width / count) * i + this.random(-19, 19);
            const y = this.random(this.height * 0.19, this.height * 0.81);
            const direction = Math.random() > 0.38 ? "up" : "down";

            candles.push({
                x,
                y,
                width,
                bodyHeight,
                wickTop,
                wickBottom,
                direction,
                drift: this.random(0.07, 0.27),
                phase: this.random(0, Math.PI * 2),
                alpha: this.random(0.27, 0.73),
            });
        }

        return candles;
    }

    createParticles(count) {
        const particles = [];

        for (let i = 0; i < count; i += 1) {
            particles.push({
                x: this.random(0, this.width),
                y: this.random(0, this.height),
                radius: this.random(2.1, 5.1),
                speedX: this.random(-0.07, 0.11),
                speedY: this.random(-0.11, -0.03),
                alpha: this.random(0.27, 0.73),
            });
        }

        return particles;
    }

    createPriceLines(count) {
        const lines = [];

        for (let i = 0; i < count; i += 1) {
            const points = [];
            const segments = 9;
            const segmentWidth = this.width / (segments - 1);
            const baseY = this.random(this.height * 0.27, this.height * 0.73);

            for (let p = 0; p < segments; p += 1) {
                points.push({
                    x: p * segmentWidth,
                    y: baseY + this.random(-57, 57),
                });
            }

            lines.push({
                points,
                speed: this.random(this.options.lineSpeed * 0.56, this.options.lineSpeed * 1.27),
                offset: this.random(-73, 73),
                alpha: this.random(0.19, 0.43),
                color: i === 0 ? this.palette.lineA : i === 1 ? this.palette.lineB : this.palette.lineC,
            });
        }

        return lines;
    }

    createSignalLocks(count) {
        const locks = [];

        for (let i = 0; i < count; i += 1) {
            locks.push({
                x: this.random(this.width * 0.15, this.width * 0.85),
                y: this.random(this.height * 0.19, this.height * 0.77),
                radius: this.random(27, 51),
                alpha: this.random(0.43, 0.73),
                growth: this.random(0.07, 0.23),
                spin: this.random(-0.019, 0.019),
                angle: this.random(0, Math.PI * 2),
            });
        }

        return locks;
    }

    random(min, max) {
        return Math.random() * (max - min) + min;
    }

    animate(timestamp) {
        const delta = this.lastTime ? Math.min(timestamp - this.lastTime, 27) : 16;
        this.lastTime = timestamp;

        this.update(delta);
        this.draw();

        this.frameId = window.requestAnimationFrame(this.animate);
    }

    update(delta) {
        const speedFactor = delta / 16;

        for (const candle of this.candles) {
            candle.phase += 0.0073 * speedFactor;
            candle.x -= candle.drift * this.options.speed * speedFactor;

            if (candle.x < -73) {
                candle.x = this.width + this.random(19, 73);
                candle.y = this.random(this.height * 0.19, this.height * 0.81);
            }
        }

        for (const particle of this.particles) {
            let repelX = 0;
            let repelY = 0;
            let hover = 0;

            if (this.pointer.active && this.pointer.x !== null && this.pointer.y !== null) {
                const dx = particle.x - this.pointer.x;
                const dy = particle.y - this.pointer.y;
                const distance = Math.hypot(dx, dy);
                const influenceRadius = 127;

                if (distance < influenceRadius) {
                    hover = 1 - distance / influenceRadius;
                    const force = hover * 2.7;
                    repelX = (dx / Math.max(distance, 1)) * force;
                    repelY = (dy / Math.max(distance, 1)) * force;
                }
            }

            particle.x += particle.speedX * this.options.particleSpeed * 15 * speedFactor + repelX * speedFactor;
            particle.y += particle.speedY * this.options.particleSpeed * 15 * speedFactor + repelY * speedFactor;
            particle.hover = hover;

            if (particle.x < -19) particle.x = this.width + 19;
            if (particle.x > this.width + 19) particle.x = -19;
            if (particle.y < -19) {
                particle.y = this.height + 19;
                particle.x = this.random(0, this.width);
            }
            if (particle.y > this.height + 19) {
                particle.y = -19;
                particle.x = this.random(0, this.width);
            }
        }

        for (const line of this.priceLines) {
            line.offset -= line.speed * speedFactor;

            if (line.offset < -73) {
                line.offset = 73;
            }
        }

        if (Math.random() < this.options.pulseChance) {
            this.pulses.push({
                x: this.random(this.width * 0.27, this.width * 0.81),
                y: this.random(this.height * 0.19, this.height * 0.73),
                radius: this.random(27, 73),
                alpha: this.random(0.07, 0.17),
                growth: this.random(0.7, 1.7),
            });
        }

        this.pulses = this.pulses
            .map((pulse) => ({
                ...pulse,
                radius: pulse.radius + pulse.growth * speedFactor,
                alpha: pulse.alpha - 0.0037 * speedFactor,
            }))
            .filter((pulse) => pulse.alpha > 0);

        this.signalLocks = this.signalLocks
            .map((lock) => {
                let hover = 0;
                let offsetX = 0;
                let offsetY = 0;

                if (this.pointer.active && this.pointer.x !== null && this.pointer.y !== null) {
                    const dx = this.pointer.x - lock.x;
                    const dy = this.pointer.y - lock.y;
                    const distance = Math.hypot(dx, dy);
                    const influenceRadius = 173;

                    if (distance < influenceRadius) {
                        hover = 1 - distance / influenceRadius;
                        const pull = hover * 11;
                        offsetX = (dx / Math.max(distance, 1)) * pull;
                        offsetY = (dy / Math.max(distance, 1)) * pull;
                    }
                }

                return {
                    ...lock,
                    angle: lock.angle + (lock.spin + hover * 0.019) * speedFactor,
                    hover,
                    drawX: lock.x + offsetX,
                    drawY: lock.y + offsetY,
                    drawRadius: lock.radius + hover * 7,
                };
            });
    }

    renderStatic() {
        this.draw(true);
    }

    draw(isStatic = false) {
        if (!this.ctx) {
            return;
        }

        this.ctx.clearRect(0, 0, this.width, this.height);

        this.drawBackgroundGlow();
        this.drawGrid();
        this.drawPriceLines();
        this.drawCandles(isStatic);
        this.drawParticles();
        this.drawPulses();
        this.drawSignalLocks();
        this.drawEdgeFade();
    }

    drawBackgroundGlow() {
        const gradient = this.ctx.createRadialGradient(
            this.width * 0.73,
            this.height * 0.27,
            11,
            this.width * 0.73,
            this.height * 0.27,
            this.width * 0.73
        );

        gradient.addColorStop(0, this.palette.bgGlow);
        gradient.addColorStop(1, "rgba(3, 11, 23, 0)");

        this.ctx.fillStyle = gradient;
        this.ctx.fillRect(0, 0, this.width, this.height);
    }

    drawGrid() {
        this.ctx.save();
        this.ctx.strokeStyle = this.palette.grid;
        this.ctx.lineWidth = 1.3;

        for (const line of this.gridLines) {
            this.ctx.beginPath();

            if (line.type === "vertical") {
                this.ctx.moveTo(line.value, 0);
                this.ctx.lineTo(line.value, this.height);
            } else {
                this.ctx.moveTo(0, line.value);
                this.ctx.lineTo(this.width, line.value);
            }

            this.ctx.stroke();
        }

        this.ctx.restore();
    }

    drawCandles(isStatic) {
        this.ctx.save();

        for (const candle of this.candles) {
            const wave = isStatic ? 0 : Math.sin(candle.phase) * 7;
            const bodyY = candle.y + wave;
            const wickTopY = bodyY - candle.wickTop;
            const wickBottomY = bodyY + candle.bodyHeight + candle.wickBottom;
            const bodyColor = candle.direction === "up" ? this.palette.candleUp : this.palette.candleDown;

            this.ctx.globalAlpha = candle.alpha * this.options.opacity;

            this.ctx.strokeStyle = this.palette.wick;
            this.ctx.lineWidth = 1.3;
            this.ctx.beginPath();
            this.ctx.moveTo(candle.x + candle.width / 2, wickTopY);
            this.ctx.lineTo(candle.x + candle.width / 2, wickBottomY);
            this.ctx.stroke();

            this.ctx.fillStyle = bodyColor;
            this.ctx.shadowBlur = 39;
            this.ctx.shadowColor = candle.direction === "up"
                ? "rgba(73, 255, 197, 0.11)"
                : "rgba(255, 111, 145, 0.09)";

            this.roundRect(
                this.ctx,
                candle.x,
                bodyY,
                candle.width,
                candle.bodyHeight,
                3
            );

            this.ctx.fill();
            this.ctx.shadowBlur = 0;
        }

        this.ctx.restore();
    }

    drawPriceLines() {
        this.ctx.save();

        for (const line of this.priceLines) {
            this.ctx.beginPath();
            this.ctx.strokeStyle = line.color;
            this.ctx.lineWidth = 2.3;
            this.ctx.globalAlpha = line.alpha * this.options.opacity;
            this.ctx.shadowBlur = 23;
            this.ctx.shadowColor = line.color;

            line.points.forEach((point, index) => {
                const x = point.x + line.offset;
                const y = point.y;

                if (index === 0) {
                    this.ctx.moveTo(x, y);
                } else {
                    this.ctx.lineTo(x, y);
                }
            });

            this.ctx.stroke();
            this.ctx.shadowBlur = 0;
        }

        this.ctx.restore();
    }

    drawParticles() {
        this.ctx.save();

        for (const particle of this.particles) {
            const hover = particle.hover || 0;

            this.ctx.beginPath();
            this.ctx.fillStyle = this.palette.particle;
            this.ctx.globalAlpha = Math.min(1, (particle.alpha + hover * 0.19) * this.options.opacity);
            this.ctx.shadowBlur = 19 + hover * 15;
            this.ctx.shadowColor = this.palette.highlight;
            this.ctx.arc(particle.x, particle.y, particle.radius + hover * 0.9, 0, Math.PI * 2);
            this.ctx.fill();
        }

        this.ctx.restore();
    }

    drawPulses() {
        this.ctx.save();

        for (const pulse of this.pulses) {
            this.ctx.beginPath();
            this.ctx.strokeStyle = this.palette.pulse;
            this.ctx.lineWidth = 2.1;
            this.ctx.globalAlpha = pulse.alpha;
            this.ctx.shadowBlur = 39;
            this.ctx.shadowColor = this.palette.pulse;
            this.ctx.arc(pulse.x, pulse.y, pulse.radius, 0, Math.PI * 2);
            this.ctx.stroke();
        }

        this.ctx.restore();
    }

    drawSignalLocks() {
        this.ctx.save();

        for (const lock of this.signalLocks) {
            const radius = lock.drawRadius || lock.radius;
            const hover = lock.hover || 0;

            this.ctx.save();
            this.ctx.translate(lock.drawX ?? lock.x, lock.drawY ?? lock.y);
            this.ctx.rotate(lock.angle);
            this.ctx.globalAlpha = Math.min(1, (lock.alpha + hover * 0.27) * this.options.opacity);
            this.ctx.strokeStyle = "rgba(127, 225, 255, 0.73)";
            this.ctx.shadowBlur = 39 + hover * 27;
            this.ctx.shadowColor = "rgba(127, 225, 255, 0.73)";

            this.ctx.beginPath();
            this.ctx.lineWidth = 1.7 + hover * 0.7;
            this.ctx.arc(0, 0, radius, 0, Math.PI * 2);
            this.ctx.stroke();

            this.ctx.beginPath();
            this.ctx.lineWidth = 1.1 + hover * 0.37;
            this.ctx.arc(0, 0, Math.max(11, radius * 0.56), 0, Math.PI * 2);
            this.ctx.stroke();

            const tick = Math.max(11, radius * 0.35);
            const gap = Math.max(7, radius * 0.23);

            this.ctx.lineWidth = 1.3 + hover * 0.37;

            this.ctx.beginPath();
            this.ctx.moveTo(-radius - tick, 0);
            this.ctx.lineTo(-radius + gap, 0);
            this.ctx.moveTo(radius - gap, 0);
            this.ctx.lineTo(radius + tick, 0);
            this.ctx.moveTo(0, -radius - tick);
            this.ctx.lineTo(0, -radius + gap);
            this.ctx.moveTo(0, radius - gap);
            this.ctx.lineTo(0, radius + tick);
            this.ctx.stroke();

            this.ctx.beginPath();
            this.ctx.fillStyle = "rgba(127, 225, 255, 0.88)";
            this.ctx.arc(0, 0, 4.1 + hover * 1.9, 0, Math.PI * 2);
            this.ctx.fill();

            this.ctx.restore();
        }

        this.ctx.restore();
    }

    drawEdgeFade() {
        const gradientBottom = this.ctx.createLinearGradient(0, this.height, 0, this.height * 0.67);
        gradientBottom.addColorStop(0, "rgba(3, 9, 19, 0.57)");
        gradientBottom.addColorStop(1, "rgba(3, 9, 19, 0)");

        this.ctx.fillStyle = gradientBottom;
        this.ctx.fillRect(0, this.height * 0.61, this.width, this.height * 0.39);

        const gradientLeft = this.ctx.createLinearGradient(0, 0, this.width * 0.19, 0);
        gradientLeft.addColorStop(0, "rgba(3, 9, 19, 0.31)");
        gradientLeft.addColorStop(1, "rgba(3, 9, 19, 0)");

        this.ctx.fillStyle = gradientLeft;
        this.ctx.fillRect(0, 0, this.width * 0.19, this.height);
    }

    roundRect(ctx, x, y, width, height, radius) {
        const safeRadius = Math.min(radius, width / 2, height / 2);

        ctx.beginPath();
        ctx.moveTo(x + safeRadius, y);
        ctx.lineTo(x + width - safeRadius, y);
        ctx.quadraticCurveTo(x + width, y, x + width, y + safeRadius);
        ctx.lineTo(x + width, y + height - safeRadius);
        ctx.quadraticCurveTo(x + width, y + height, x + width - safeRadius, y + height);
        ctx.lineTo(x + safeRadius, y + height);
        ctx.quadraticCurveTo(x, y + height, x, y + height - safeRadius);
        ctx.lineTo(x, y + safeRadius);
        ctx.quadraticCurveTo(x, y, x + safeRadius, y);
        ctx.closePath();
    }
}

function initMarketBackground() {
    const canvas = document.querySelector("[data-market-background]");

    if (!canvas) {
        return;
    }

    return new MarketBackground(canvas);
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initMarketBackground);
} else {
    initMarketBackground();
}

export default MarketBackground;
