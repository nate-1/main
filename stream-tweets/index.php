<title>Stream tweets</title>
<link href="https://fonts.googleapis.com/css?family=Roboto+Mono" rel="stylesheet">

<style>
ul#console {
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

</style>

<ul id="console">
<?php

    function makeUrltoLink($string) {
      // The Regular Expression filter
      $reg_pattern = "/(((http|https|ftp|ftps)\:\/\/)|(www\.))[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\:[0-9]+)?(\/\S*)?/";
      
      // make the urls to hyperlinks
      return preg_replace($reg_pattern, '<a href="$0" target="_blank" rel="noopener noreferrer">$0</a>', $string);
    }

    $ACCOUNT_ARRAY = ["TechCrunch", "CNBC", "NY Times", "Le Monde"];

    $content = file_get_contents('/home/nate/prog_data/temp/tweetstream/tweethistory.json'); 
    $json = json_decode($content, true);
    $len = count($json);

    for($i =0 ; $i < $len; $i++) {
      $tag = intval($json[$i]["tag"]);
    
      echo "<li>";
        echo  "<i class='time'>" . $json[$i]["time"] . "</i> ";
        echo  "<i class='name'>" . $ACCOUNT_ARRAY[$tag - 1] . "</i> ";
        echo "<span class='color$tag'>" . makeUrltoLink($json[$i]["content"]) . "</span>";
      echo "</li>";
    }

  ?>   
</ul>

<script>
// adds entry to the html #console
const CONSOLE_HTML = document.getElementById('console')
const ACCOUNT_ARRAY = ["TechCrunch", "CNBC", "NY Times", "Le Monde"]
const COLOR_ARRAY = ""
let socket

function log(event){
  let scrollVal = window.scrollY
  let height = CONSOLE_HTML.offsetHeight 

  if(!event.data) {
    return
  }

  json = JSON.parse(event.data)

  if(!json["content"]) {
    return
  }

  console.log(json)

  let shouldScrollD = false
  if(scrollVal - height + window.innerHeight < 100) {
    shouldScrollD = true
  }

  const main = document.createElement("li")

  const time = document.createElement('i')
  time.textContent = json.time + ' '
  time.className = 'time'
  main.appendChild(time)

  const account = document.createElement('i') 
  account.textContent = ACCOUNT_ARRAY[parseInt(json.tag) - 1]
  account.className = 'name'
  main.appendChild(account)


  const content = document.createElement('span')
  content.innerHTML = ' ' + autolink(json.content)
  content.className = 'color' + json.tag
  main.appendChild(content)

  CONSOLE_HTML.appendChild(main)
    
  if(shouldScrollD) {
    window.scrollTo(0, height)
  }
}

function createWebSocket() {
  socket = new WebSocket("ws://nategus.com:42883")

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
    log(event)
  });

  
}

function autolink(s) {

  return s.replaceAll( /(((http|https|ftp|ftps)\:\/\/)|(www\.))[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\:[0-9]+)?(\/\S*)?/g,
    (match, p1, p2, p3, p4) => {
      matchSplitted = match.split('/')
      return '<a href="'+  match + '" target="_blank" rel="noopener noreferrer">'+ matchSplitted[matchSplitted.length - 1] + '</a>'
    } 

  );   
}

createWebSocket();
// Send a msg to the websocket

</script>