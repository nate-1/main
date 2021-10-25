<title>Stream subtitles</title>
<link href="https://fonts.googleapis.com/css?family=Roboto+Mono" rel="stylesheet">

<style>
ul#console, div {
  list-style-type: none;
  font-family: 'Roboto Mono', verdana;
  font-size: 14px;
  line-height: 25px;
  padding-left: 5px;
}
ul#console li {
  border-bottom: solid 1px #80808038;
  color: white;
}
li {
  padding-top: 10px;
  padding-bottom: 10px;
}
html {
    background-color: #181818;
}

i.time {
  color: red
}

i.name {
  color: #3cb44b
}

.color1 {
  color: cyan
}

.color2 {
  color: magenta
}

.color3 {
  color: lime
}

.color4 {
  color: yellow
}

.color5 {
  color: white
}

a {
  color: inherit;
}

body, html {
  margin: 0;
  padding: 0;
}

</style>

<div style="position: fixed; width: 100%; height: auto; background-color: #404040; ">
  <div style="float: left; padding: 5px 0 5px 0">
    <button onclick="askForDelay()">Set the delay</button>
  </div>
  <div style="float: right; padding-right: 10px;">
    Current delay:  <span id="delay"></span>
  </div>
</div>
<ul id="console" style="padding-top: 35px;">
  
</ul>

  <p id="info" style="color: whitesmoke;"></p>
<script>
// adds entry to the html #console
const CONSOLE_HTML = document.getElementById('console')
const CURRENT_DELAY = document.getElementById('delay')
const PART_TO_WAIT = 1000;


let socket
let delay = 192000


function handleEvent(event){

  if(!event.data) {
    return
  }

  json = JSON.parse(event.data)

  processJson(json)
}

function processJson(json) {

  if(!json["content"] || json["content"].length === 0) {
    console.log(convertDate(json.start_time) + ' -> ' + convertDate(json.end_time), "empty")
    return
  }

  console.log(json)
  const startUnixTimestamp = new Date(json.start_time).getTime()
  console.log(startUnixTimestamp)
  handleDelaying(json, startUnixTimestamp)

}

function handleDelaying(json, startUnixTimestamp) {
  const currentUnixTimestamp = Date.now()
  const timeToWait = (startUnixTimestamp + delay) - currentUnixTimestamp

  if(timeToWait <= 0) {
    console.log('triggered')
    writeLog(json)
    return;
  }

  const realTimeToWait = (timeToWait >  PART_TO_WAIT ? PART_TO_WAIT : timeToWait)

  setTimeout(() => handleDelaying(json, startUnixTimestamp), realTimeToWait)
}

function writeLog(json) {
  
  const main = document.createElement("li")
  
  const time = document.createElement('i')
  time.textContent = convertDate(json.start_time) + ' -> ' + convertDate(json.end_time)
  time.className = 'time'
  main.appendChild(time)
  
  const content = document.createElement('span')
  
  let contentString = '<br>'
  for(const item of json.content) {
    contentString += item + '<br>';
  }
  content.innerHTML = contentString
  main.appendChild(content)
  
  
  let scrollVal = window.scrollY
  let height = CONSOLE_HTML.offsetHeight 
  let shouldScrollD = false
  if(scrollVal - height + window.innerHeight < 100) {
    shouldScrollD = true
  }

  CONSOLE_HTML.appendChild(main)
    
  if(shouldScrollD) {
    window.scrollTo(0, height)
  }
  
}

function createWebSocket() {
  socket = new WebSocket("ws://nategus.com:27279")

  socket.addEventListener('open', function (event) {
    window.scrollTo(0, CONSOLE_HTML.offsetHeight);
    console.log('Connected to the WS Server!')
  });

  // Connection closed
  socket.addEventListener('close', function (event) {
      console.log('Disconnected from the WS Server!')
      setTimeout(() => createWebSocket(), 10000)
  });

  // Listen for messages
  socket.addEventListener('message', function (event) {
    handleEvent(event)
  });

}


function convertDate(stringValue) {
  const dateObj = new Date(stringValue)
  return dateObj.toLocaleTimeString()
}

function askForDelay() {
  delay = parseInt(prompt("What delay do you want? (in ms)", delay)) ?? 0
  CURRENT_DELAY.textContent = delay
}

function initValueFromInitArray() {
  let initArray = [
    <?php
      $content = file_get_contents("/home/nate/prog_data/temp/IPTVSubtitleStream/subhistory.json");
      $json = json_decode($content, true);
      $lenMain = count($json);
      
      for($i = 0 ; $i < $lenMain; $i++) {
        echo '{ '; 
          echo 'start_time: "' . $json[$i]['start_time'] . '", ';
          echo 'end_time: "' . $json[$i]['end_time'] . '", ';
          $lenContent = count($json[$i]['content']); 
          echo 'content: [';
          for($n = 0; $n < $lenContent; $n++) {
            echo '"' . str_replace('"', '\"', $json[$i]['content'][$n]) . '"';
            if($n != $lenContent-1) {
              echo ', ';
            }
          }
          echo ' ]';
          echo ' }';
          if($i != $lenMain-1) {
            echo ', ';
          }
          
        }
      ?>
  ]
  console.log('init array')
  console.log(initArray)
  for(const sub of initArray) {
    processJson(sub)
  }
}

askForDelay()
initValueFromInitArray()
createWebSocket()
// Send a msg to the websocket

</script>