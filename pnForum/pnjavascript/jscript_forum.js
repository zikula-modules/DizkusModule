
function x () {
return;
}

function DoSmilie(SmilieCode) {

var SmilieCode;
var revisedMessage;
var currentMessage = document.post.message.value;
revisedMessage = currentMessage+SmilieCode;
document.post.message.value=revisedMessage;
document.post.message.focus();
return;
}

function DoPrompt(action) {
var revisedMessage;
var currentMessage = document.post.message.value;

if (action == "url") {
var thisURL = prompt(""._PNFORUM_BBCODE_ENTER_URL."", "http://");
var thisTitle = prompt(""._PNFORUM_BBCODE_ENTER_SITE_TITLE."", ""._PNFORUM_BBCODE_ENTER_PAGE_TITLE."");
var urlBBCode = "[URL="+thisURL+"]"+thisTitle+"[/URL]";
revisedMessage = currentMessage+urlBBCode;
document.post.message.value=revisedMessage;
document.post.message.focus();
return;
}

if (action == "email") {
var thisEmail = prompt(""._PNFORUM_BBCODE_ENTER_EMAIL_ADDRESS."", "");
var emailBBCode = "[EMAIL]"+thisEmail+"[/EMAIL]";
revisedMessage = currentMessage+emailBBCode;
document.post.message.value=revisedMessage;
document.post.message.focus();
return;
}

if (action == "bold") {
var thisBold = prompt(""._PNFORUM_BBCODE_ENTER_TEXT_BOLD."", "");
var boldBBCode = "[B]"+thisBold+"[/B]";
revisedMessage = currentMessage+boldBBCode;
document.post.message.value=revisedMessage;
document.post.message.focus();
return;
}

if (action == "italic") {
var thisItal = prompt(""._PNFORUM_BBCODE_ENTER_TEXT_ITALIC."", "");
var italBBCode = "[I]"+thisItal+"[/I]";
revisedMessage = currentMessage+italBBCode;
document.post.message.value=revisedMessage;
document.post.message.focus();
return;
}

if (action == "image") {
var thisImage = prompt(""._PNFORUM_BBCODE_ENTER_WEBIMAGE_URL."", "http://");
var imageBBCode = "[IMG]"+thisImage+"[/IMG]";
revisedMessage = currentMessage+imageBBCode;
document.post.message.value=revisedMessage;
document.post.message.focus();
return;
}

if (action == "quote") {
var quoteBBCode = "[QUOTE]  [/QUOTE]";
revisedMessage = currentMessage+quoteBBCode;
document.post.message.value=revisedMessage;
document.post.message.focus();
return;
}

if (action == "code") {
var codeBBCode = "[CODE]  [/CODE]";
revisedMessage = currentMessage+codeBBCode;
document.post.message.value=revisedMessage;
document.post.message.focus();
return;
}

if (action == "listopen") {
var liststartBBCode = "[LIST]";
revisedMessage = currentMessage+liststartBBCode;
document.post.message.value=revisedMessage;
document.post.message.focus();
return;
}

if (action == "listclose") {
var listendBBCode = "[/LIST]";
revisedMessage = currentMessage+listendBBCode;
document.post.message.value=revisedMessage;
document.post.message.focus();
return;
}

if (action == "listitem") {
var thisItem = prompt(""._PNFORUM_BBCODE_ENTER_LIST_ITEM."", "");
var itemBBCode = "[*]"+thisItem;
revisedMessage = currentMessage+itemBBCode;
document.post.message.value=revisedMessage;
document.post.message.focus();
return;
}

}
