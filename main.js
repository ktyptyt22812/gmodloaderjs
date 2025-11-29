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
var INFO = Color(45, 255, 99);

iDownloading = false;
iFileCount = false;
files_downloaded = 0;

var remaining_elem;
var remaining_logline;

var mdl_cull = ['.vtx', ".dx80.vtx", ".dx90.vtx", '.mdl', '.sw.vtx', '.phy', '.vvd'];
var ext_iconmap = {
	"vmt": "photo_link",
	"vtf": "picture_link",
	"png": "picture_link",
	"jpg": "picture_link",
	"wav": "sound",
	"mp3": "sound",
	"ogg": "sound",
	"mdl": "brick_link",
};

function getExtension(path) {
	var basename = path.split(/[\\/]/).pop(), // extract file name from full path ...
		// (supports `\\` and `/` separators)
		pos = basename.lastIndexOf("."); // get last position of `.`

	if (basename === "" || pos < 1) // if file name is empty or ...
		return ""; //  `.` not found (-1) or comes first (0)

	return basename.slice(pos + 1); // extract extension ignoring `.`
}
var prev_dl;
var lastdllog_num;
var lastdllog;

function UpdateDownloading(a) {

	var remaining = iDownloading - files_downloaded;

	if (remaining < -2) {
		if (remaining_elem) {
			remaining_elem.remove();
		} else {
			return;
		}
	}

	if (!remaining_logline) {
		remaining_logline = LogNoRemove(INFO, "Files remaining ", WHITE, remaining);
		remaining_elem = remaining_logline.children().last();
	};

	remaining_elem.text(remaining > 0 && remaining || 0);
	var ext = getExtension(a);
	var str = a.replace(/\//g, " ").replace(/_/g, " ");

	for (var i = mdl_cull.length; i--;) {
		var suffix = mdl_cull[i];
		if (str.endsWith(suffix)) {

			str = str.substring(0, str.length - suffix.length);
			ext = "mdl";
			break;
		};
	};

	if (str == "") {
		console.log("WTF? " + a);
	}
	if (prev_dl == str) {
		if (lastdllog) {
			if (lastdllog_num) {
				lastdllog_num += 1;
				lastdllog.children().last().text(" (" + lastdllog_num + ")");
			} else {
				lastdllog_num = 2;
				lastdllog.append($("<span>").text(" (2)"));
			}
		};
		return;
	}
	prev_dl = str;
	lastdllog_num = false;
	lastdllog = Log(Icon16(ext_iconmap[ext] || "world_go"), " ", str);
};


function OnExtraInfo(data, textStatus, request, same_instance) {
	if (!same_instance) {
		OnServerCrashed();
	};
	OnStats(data.stats);
}

var remaininglua_logline;
var remaininglua_elem;

function OnStatus(a) {

	if (a == "Retrieving Workshop file details...") {
		return;
	};

	if (a == "Deleting Leftovers") {
		return;
	};
	if (a == "Mounting Addons") {
		return;
	};
	if (a == "Workshop Complete") {
		return;
	};
	if (a == "Sending client info...") {
		return;
	};

	if (a == "Client info sent!") {
		return;
	};

	if (a == "Received all Lua files we needed!") {
		return;
	};

	if (a.indexOf("lua files from the server") > 0) {
		return;
	};

	var m = a.match(/Downloaded (\d{1,4}) of (\d{1,4}) Lua files/);
	if (m && m[2]) {

		if (!remaininglua_logline) {
			remaininglua_logline = LogNoRemove(Icon16("script_link"), INFO, "Downloading Lua ", WHITE, m[1], "/", m[2]);
			remaininglua_elem = remaininglua_logline.children().last().prev().prev();
		};

		remaininglua_elem.text(m[1]);

		return;
	};

	var m = a.match(/Loading '(.*)'$/);
	if (m && m[1]) {

		Log(Icon16("plugin"), INFO, "Workshop: ", WHITE, m[1]);

		return;
	};

	Log(a);
};

function DoGmodQueue(entry) {
	var a = entry[1];
	var b = entry[2];
	var c = entry[3];

	switch (entry[0]) {
		case DOWNLOAD_FILES:
			files_downloaded++;
			UpdateDownloading(a);
			break;
		case STATUS_CHANGED:
			OnStatus(a);
			break;
		case FILES_NEEDED:
			if (a != iDownloading) {
				Log(INFO, "Files needed ", WHITE, a, b, c);
			}
			iDownloading = a;
			break;
		case FILES_TOTAL:
			if (a != iFileCount) {
				Log(INFO, "Files total ", WHITE, a, b, c);
			}
			iFileCount = a;
			break;
		default:
			LogD("???", a, b, c);
	}

}

function OnGmodQueue() {
	while (gmod_queue.length > 0) {
		var entry = gmod_queue.pop();
		DoGmodQueue(entry);
	};
};


function OnImagesLoaded(res) {
	if (!res) return;
	var imageslist = res['result'];
	if (!imageslist) return;

	var t = [];
	for (key in imageslist) {
		var dat = imageslist[key];
		var approved = dat['approval'];
		if (!approved) {
			continue;
		}
		var creator = dat['comment'] || dat['name'];
		var url = dat['url'];
		t.push([url, creator || ""]);
	}
	shuffle(t);
	//LogD("OnImagesLoaded " + t.length);

	if (t.length == 0) {
		return;
	};

	var img1 = document.getElementById("img1");
	var img2 = document.getElementById("img2");

	var imgn = 0;

	function ImageLoadLoop() {
		var rndimage = t[imgn];
		if (!rndimage) {
			imgn = 0;
			rndimage = t[0];
		}
		var first = imgn == 0;
		imgn = imgn + 1;

		var src = rndimage[0];
		src=src.replace("images.akamai.steamusercontent.com","steamuserimages-a.akamaihd.net")
		if (src.indexOf("://")==-1) {
			src = "https://"+src;
		}
		var credits = document.getElementById("credits");
		var img = new Image();
		img.onerror = function () {
			setTimeout(ImageLoadLoop, 8000);
		};
		img.onload = function () {
			var target = img1;
			if (first) {
				$(target).hide();
				$(target).fadeIn(2000, function (data) {
					HideLogo();
				});
				target.style.backgroundRepeat = 'no-repeat';
				target.style.backgroundPosition = 'center';
				target.style.backgroundSize = 'cover';
			};

			target.style.backgroundImage = "url('" + this.src + "')";

			setTimeout(ImageLoadLoop, first && 10000 || 8000);

			credits.textContent = rndimage[1] || "";
		}
		img.src = src;

	}
	ImageLoadLoop();

}

function OnServerCrashed() {
	Log("Server", Color(255, 22, 20), " CRASHED", Color(255, 2222, 255), ", reconnect manually!");
}

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
const bgImages = ["bg1.jpeg", "bg2.jpeg", "bg3.jpeg"];
let loadedImages = [];
let currentBg = 0;
let lastBgSwitch = performance.now();
const bgSwitchInterval = 8000; // каждые 8 сек
for (let src of bgImages) {
  let img = new Image();
  img.src = src;
  loadedImages.push(img);
}
function drawBackground(now) {
  let nextBg = (currentBg + 1) % loadedImages.length;
  let t = (now - lastBgSwitch) / bgSwitchInterval;

  if (t >= 1) {
    currentBg = nextBg;
    lastBgSwitch = now;
    t = 0;
  }

  // текущая картинка
  ctx.globalAlpha = 1;
  ctx.drawImage(loadedImages[currentBg], 0, 0, canvas.width, canvas.height);

  // плавный переход к следующей
  if (t > 0.7) {

    ctx.drawImage(loadedImages[nextBg], 0, 0, canvas.width, canvas.height);
  }
  ctx.fillStyle = "rgba(0,0,0,0.4)"; // 0.4 = 40% прозрачности
  ctx.fillRect(0, 0, canvas.width, canvas.height);
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
  drawBackground(performance.now());

for (let i = 0; i < orbCount; i++) {
    orbStates[i] = orbStates[i] || { progress: 0 };
    const delay = i * delayBetween;
    const startTime = orbitStartTime + delay;

    const timeOffset = orbPhases[i];
    const orbitSpeed = 0.8;
    
    const vertAngle = Math.sin(now * orbitSpeed + timeOffset) * Math.PI * 0.6;
    
    const horizAngle = (now * orbitSpeed * 1.5 + timeOffset + i * (Math.PI * 2 / orbCount)) % (Math.PI * 2);
    
    const sphereExpansion = 1 + Math.sin(now * 0.3) * 0.3;
    const sphereRadius = baseRadius * sphereExpansion;
    

    const x3d = Math.cos(horizAngle) * Math.cos(vertAngle);
    const y3d = Math.sin(vertAngle);
    const z3d = Math.sin(horizAngle) * Math.cos(vertAngle);
    
    const targetX = centerX + x3d * sphereRadius;
    const targetY = centerY + y3d * sphereRadius * 0.6; 
    
    if (now >= startTime) {
      const progress = Math.min((now - startTime) / flyDuration, 1);
      orbStates[i].progress = progress < 0.5 
        ? 2 * progress * progress 
        : 1 - Math.pow(-2 * progress + 2, 2) / 2;
    }

    const progress = orbStates[i].progress;
    const currentX = centerX - 1000 + (targetX - (centerX - 1000)) * progress;
    const currentY = centerY - 400 + (targetY - (centerY - 400)) * progress;
    const depthScale = 0.7 + z3d * 0.3;
    const size3D = size * depthScale;
    
    const pulse = 0.85 + Math.sin(now * 2 + timeOffset) * 0.15;
    const depthAlpha = 0.5 + z3d * 0.5; 
    const alpha = pulse * depthAlpha * progress;
    
    const spriteSpin = (now * 120 + i * 80) % 360;
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

