/**
 * WPOS Websocket update relay, node.js server.
 * @type {*}
 */

import http from "http";
import "dotenv/config";

const server = http.createServer();
const port = process.env.FEEDSERV_PORT || 3000;
const ip = process.env.FEEDSERV_IP || "0.0.0.0";
const hashkey = process.env.FEEDSERV_KEY || "supersecretkey";

server.listen(port, ip);
import { Server } from "socket.io";
const io = new Server(server, {
  cors: { origin: "*", methods: ["GET", "POST"] },
});

const devices = {};
const sessions = {};

io.on("connection", function (socket) {
  // START AUTHENTICATION
  let cookies = null;
  let authed = false;
  // check for session cookie
  if (socket.handshake.hasOwnProperty("headers")) {
    if (socket.handshake.headers.hasOwnProperty("cookie")) {
      cookies = socket.handshake.headers.cookie;
      if (cookies.indexOf("PHPSESSID=") !== -1) {
        // trim up to our cookie value
        cookies = cookies.substring(cookies.indexOf("PHPSESSID=") + 10);
        if (cookies.indexOf(";") !== -1) {
          // trim off other cookies
          cookies = cookies.substring(0, cookies.indexOf(";"));
        }
      }
      if (sessions.hasOwnProperty(cookies)) {
        authed = true;
        // Request device registration
        socket.emit("updates", { a: "regreq", data: "" });
        console.log("Authorised by session: " + cookies);
      }
    }
  }

  console.log(socket.handshake.query);
  // check for hashkey (for php authentication)
  if (!authed) {
    if ("hashkey" in socket.handshake.query) {
      if (hashkey == socket.handshake.query.hashkey) {
        authed = true;
        console.log(`Authorised (${socket.request.socket.remoteAddress}) by hashkey: ${socket.handshake.query.hashkey}`);
      }
    }
  }

  socket.emit("updates", { a: "regreq", data: "" });
  console.log("New connection from " + socket.request.socket.remoteAddress);
  // Request device registration
  // Disconnect if not authenticated
  console.log(authed)
  if (!authed) {
    console.log("Socket authentication failed for " + socket.request.socket.remoteAddress);
    //   socket.emit("updates", { a: "error", data: { code: "auth", message: "Socket authentication failed!" } });
    //   socket.disconnect();
  }

  // broadcast to all connected sockets
  socket.on("broadcast", function (data) {
    socket.broadcast.emit("updates", data);
  });

  // send to certain auth'd devices based on device id's provided.
  socket.on("send", function (data) {
    let updateData = { data: data.include, a: data.data.a };

    // if device.include is null, send to all auth'd
    const inclall = data.include == null;
    for (const i in devices) {
      if (inclall || (data.include && Object.prototype.hasOwnProperty.call(data.include, i))) {
        const targetSocket = io.sockets.sockets.get(devices[i].socketid);
        if (targetSocket) {
          targetSocket.emit("updates", updateData);
        }
      } else {
        console.log(i + " not in devicelist, " + JSON.stringify(data.include) + "; discarding.");
      }
    }
    // send to the admin dash
    if (devices.hasOwnProperty(0)) {
      // send updated device list to admin dash
      const adminSocket = io.sockets.sockets.get(devices[0].socketid);
      if (adminSocket) {
        adminSocket.emit("updates", updateData);
      }
    }
  });

  socket.on("session", function (data) {
    // check for hashkey
    if (hashkey == data.hashkey) {
      if (data.remove == false) {
        sessions[data.data] = true;
        console.log("Added PHP session: " + data.data);
      } else {
        if (sessions.hasOwnProperty(data.data)) {
          delete sessions[data.data];
          console.log("Removed PHP session: " + data.data);
        }
      }
    } else {
      console.log("Send request not processed, no valid hashkey!");
    }
  });

  socket.on("hashkey", function (data) {
    // check for hashkey
    if (hashkey == data.hashkey) {
      hashkey = data.newhashkey;
    } else {
      console.log("Send request not processed, no valid hashkey!");
    }
  });

  // register device details
  socket.on("reg", function (request) {
    // register device
    devices[request.deviceid] = {};
    devices[request.deviceid].socketid = socket.id;
    devices[request.deviceid].username = request.username;
    // remove device on disconnect
    socket.on("disconnect", function () {
      delete devices[request.deviceid];
      if (request.deviceid != 0) {
        if (devices.hasOwnProperty(0)) {
          // send updated device list to admin dash
          const adminSocket = io.sockets.sockets.get(devices[0].socketid);
          if (adminSocket) {
            adminSocket.emit("updates", { a: "devices", data: JSON.stringify(devices) });
          }
        }
      }
    });
    if (devices.hasOwnProperty(0)) {
      // send updated device list to admin dash
      const adminSocket = io.sockets.sockets.get(devices[0].socketid);
      if (adminSocket) {
        adminSocket.emit("updates", { a: "devices", data: JSON.stringify(devices) });
      }
    }
    console.log("Device registered");
  });
});