const canvas = document.getElementById("loaderCanvas");
const ctx = canvas.getContext("2d");

canvas.width = window.innerWidth;
canvas.height = window.innerHeight;
let gmodInfo = {
  servername: "",
  serverurl: "",
  mapname: "",
  maxplayers: 0,
  steamid: "",
  gamemode: "",
  volume: 1,
  language: ""
};
let currentDownloadingFile = "";

let spinAngle = 0;
let baseRadius = 150;
let flyDuration = 1.2;
let delayBetween = 0.15;
let orbitStartTime = performance.now() / 1000;
let orbStates = [];
let logMessages = [];
function DownloadingFile(fileName) {
  currentDownloadingFile = fileName;
  addLog("Downloading " + fileName);
}

function GameDetails(servername, serverurl, mapname, maxplayers, steamid, gamemode, volume, language) {
  gmodInfo.servername = servername;
  gmodInfo.mapname = mapname;
  addLog("Connected to " + servername);
  addLog("Map: " + mapname);
}

const orbCount = 7;
const orbOptions = ["dev", "", "","","","",""];
const glowImage = new Image();
glowImage.src = "orb.png";
function addLog(message) {
  if (logMessages.length > 15) {
    logMessages.shift(); 
  }
  logMessages.push(message);
  console.log("[LOG]", message);
}
function drawLog(x, y) {
  ctx.font = "18px monospace";
  ctx.textAlign = "left";
  for (let i = 0; i < logMessages.length; i++) {
    ctx.fillStyle = "rgba(255,255,255,0.9)";
    ctx.fillText("> " + logMessages[i], x, y + i * 20);
  }
}

function drawOrb(x, y, size, alpha, angle) {
  ctx.save();
  ctx.globalAlpha = alpha;
  ctx.translate(x, y);
  ctx.rotate((angle * Math.PI) / 180);
  ctx.drawImage(glowImage, -size / 2, -size / 2, size, size);
  ctx.restore();
}
function GameDetails(servername, serverurl, mapname, maxplayers, steamid, gamemode, volume, language) {
  gmodInfo.servername = servername;
  gmodInfo.serverurl = serverurl;
  gmodInfo.mapname = mapname;
  gmodInfo.maxplayers = maxplayers;
  gmodInfo.steamid = steamid;
  gmodInfo.gamemode = gamemode;
  gmodInfo.volume = volume;
  gmodInfo.language = language;

  console.log("GMod connected:", gmodInfo);
}
function DownloadingFile(fileName) {
  currentDownloadingFile = fileName;
  console.log("Скачивается:", fileName);
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

  ctx.clearRect(0, 0, canvas.width, canvas.height);

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
  drawLog(30, canvas.height - 200);

    drawOrb(currentX, currentY, size3D, alpha, spriteSpin);
    ctx.fillStyle = "#fff";
    ctx.font = "24px sans-serif";
    ctx.textAlign = "center";
    ctx.fillText(`${gmodInfo.servername}`, canvas.width / 2, canvas.height - 100);
    ctx.fillText(`Map: ${gmodInfo.mapname}`, canvas.width / 2, canvas.height - 70);
    ctx.fillText(`${currentDownloadingFile}`, canvas.width / 2, canvas.height - 40);

  }
}
animate();
