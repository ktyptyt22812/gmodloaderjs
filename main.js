const canvas = document.getElementById("loaderCanvas");
const ctx = canvas.getContext("2d");

canvas.width = window.innerWidth;
canvas.height = window.innerHeight;

let spinAngle = 0;
let baseRadius = 150;
let flyDuration = 1.2;
let delayBetween = 0.15;
let orbitStartTime = performance.now() / 1000;
let orbStates = [];

const orbCount = 7;
const orbOptions = ["dev", "", "","","","",""];
const glowImage = new Image();
glowImage.src = "orb.jpeg";
const bgImages = [
  "bg1.jpeg",
  "bg2.jpeg",
  "bg3.jpeg"
].map(src => {
  const img = new Image();
  img.src = src;
  return img;
});

let currentBG = 0;
let nextBG = 1;
let bgAlpha = 0;
let bgSwapTime = 20; // секунд до смены
let bgFadeDuration = 3; // секунд на переход
let bgTimer = performance.now();

function drawBackground(time) {
  const delta = (time - bgTimer) / 1000;

  if (delta >= bgSwapTime) {
    bgTimer = time;
    currentBG = nextBG;
    nextBG = (nextBG + 1) % bgImages.length;
    bgAlpha = 0;
  }

  // Fade in next image
  if (delta >= (bgSwapTime - bgFadeDuration)) {
    bgAlpha = (delta - (bgSwapTime - bgFadeDuration)) / bgFadeDuration;
    bgAlpha = Math.min(bgAlpha, 1);
  }

  // Draw current
  ctx.globalAlpha = 1;
  ctx.drawImage(bgImages[currentBG], 0, 0, canvas.width, canvas.height);

  // Draw next with fade
  if (bgAlpha > 0) {
    ctx.globalAlpha = bgAlpha;
    ctx.drawImage(bgImages[nextBG], 0, 0, canvas.width, canvas.height);
  }

  ctx.globalAlpha = 0.3; // reset
}

function drawOrb(x, y, size, alpha, angle) {
  ctx.save();
  ctx.globalAlpha = alpha;
  ctx.translate(x, y);
  ctx.rotate((angle * Math.PI) / 180);
  ctx.drawImage(glowImage, -size / 2, -size / 2, size, size);
  ctx.restore();
}

function drawCube(x, y, size, depth) {
  const perspective = 0.5 + depth * 0.3;
  const w = size * perspective;
  const h = size * perspective;

  ctx.save();
  ctx.translate(x, y);

  const grad = ctx.createLinearGradient(-w/2, -h/2, w/2, h/2);
  grad.addColorStop(0, "#222");
  grad.addColorStop(0.5, "#666");
  grad.addColorStop(1, "#aaa");

  ctx.fillStyle = grad;
  ctx.strokeStyle = "#fff";
  ctx.lineWidth = 1;
  ctx.globalAlpha = 0.2 + depth * 0.3;

  ctx.beginPath();
  ctx.moveTo(-w/2, -h/2);
  ctx.lineTo(w/2, -h/2);
  ctx.lineTo(w/2, h/2);
  ctx.lineTo(-w/2, h/2);
  ctx.closePath();
  ctx.fill();
  ctx.stroke();

  ctx.restore();
}

function drawText(text, x, y, selected = false) {
  ctx.fillStyle = selected ? "#aaf" : "#fff";
  ctx.font = "20px sans-serif";
  ctx.textAlign = "left";
  ctx.fillText(text, x, y);
}

function animate() {
  requestAnimationFrame(animate);
  const now = performance.now() / 1000;
  const elapsed = now - orbitStartTime;
  spinAngle = (spinAngle + 90 * (1 / 60)) % 360;

  drawBackground(performance.now());


  const centerX = canvas.width / 2;
  const centerY = canvas.height / 2 - 100;
  const size = 64;

  for (let i = 0; i < orbCount; i++) {
    orbStates[i] = orbStates[i] || { progress: 0 };
    const delay = i * delayBetween;
    const startTime = orbitStartTime + delay;

    const angle = (spinAngle + i * 45) * (Math.PI / 180); // должно работать

    const targetX = centerX + Math.cos(angle) * baseRadius;
    const ellipseY = baseRadius * 0.5 + Math.sin(now * 2) * 90;
    const targetY = centerY + Math.sin(angle) * ellipseY;

    if (now >= startTime) {
      const progress = Math.min((now - startTime) / flyDuration, 1);
      orbStates[i].progress = progress;
    }

    const progress = orbStates[i].progress;
    const currentX = centerX - 1000 + (targetX - (centerX - 1000)) * progress;
    const currentY = centerY - 400 + (targetY - (centerY - 400)) * progress;

    const depth = Math.sin(angle);
    const size3D = size * (1 + depth * 0.3);
    const alpha = 255;
    const spriteSpin = (now * 180 + i * 140) % 360;

    drawOrb(currentX, currentY, size3D, alpha, spriteSpin);
  }
}
animate();
