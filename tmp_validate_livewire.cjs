const http = require("http");
const url = "http://127.0.0.1:8000/livewire-725891d4/livewire.js?id=657d57c2";
http.get(url, res => {
  let body = "";
  res.on("data", chunk => body += chunk);
  res.on("end", () => {
    try {
      new Function(body);
      console.log("valid");
    } catch (e) {
      console.error(e.name + ": " + e.message);
      console.error(e.stack);
    }
  });
});
