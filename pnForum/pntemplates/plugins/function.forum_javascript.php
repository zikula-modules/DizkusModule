<?php
// ----------------------------------------------------------------------
// Purpose of file: display smilies and bbcodes in forum
// ----------------------------------------------------------------------

/**
 * forum_javascript 
 * adds some javascript needed for smilies and bbcode
 *
 */
function smarty_function_forum_javascript($params, &$smarty) 
{
    extract($params); 
	unset($params);

    // get lang defines
    pnModAPILoad('pnForum', 'user');
    $out = "<script type=\"text/javascript\">\n";
    $out.= "//<![CDATA[\n";
    $out.= "function x () {\n";
    $out.= "return;\n";
    $out.= "}\n";
    $out.= "\n";
    $out.= "function DoSize (fontsize) {\n";
    $out.= "var revisedMessage;\n";
    $out.= "var post = document.getElementById(\"post\");\n";
    $out.= "var currentMessage = post.message.value;\n";
    $out.= "var sizeBBCode = \"[size=\"+fontsize+\"][/size]\";\n";
    $out.= "revisedMessage = currentMessage+sizeBBCode;\n";
    $out.= "post.message.value=revisedMessage;\n";
    $out.= "post.message.focus();\n";
    $out.= "return;\n";
    $out.= "}\n";
    $out.= "\n";
    $out.= "function DoColor (fontcolor) {\n";
    $out.= "var revisedMessage;\n";
    $out.= "var post = document.getElementById(\"post\");\n";
    $out.= "var currentMessage = post.message.value;\n";
    $out.= "var colorBBCode = \"[color=\"+fontcolor+\"][/color]\";\n";
    $out.= "revisedMessage = currentMessage+colorBBCode;\n";
    $out.= "post.message.value=revisedMessage;\n";
    $out.= "post.message.focus();\n";
    $out.= "return;\n";
    $out.= "}\n";
    $out.= "\n";
    $out.= "function DoSmilie(SmilieCode) {\n";
    $out.= "\n";
    $out.= "var SmilieCode;\n";
    $out.= "var revisedMessage;\n";
    $out.= "var post = document.getElementById(\"post\");\n";
    $out.= "var currentMessage = post.message.value;\n";
    $out.= "revisedMessage = currentMessage+SmilieCode;\n";
    $out.= "post.message.value=revisedMessage;\n";
    $out.= "post.message.focus();\n";
    $out.= "return;\n";
    $out.= "}\n";
    $out.= "\n";
    $out.= "function DoPrompt(action) {\n";
    $out.= "var revisedMessage;\n";
    $out.= "var post = document.getElementById(\"post\");\n";
    $out.= "var currentMessage = post.message.value;\n";
    $out.= "\n";
    $out.= "if (action == \"url\") {\n";
    $out.= "var thisURL = prompt(\""._PNFORUM_BBCODE_ENTER_URL."\", \"http://\");\n";
    $out.= "var thisTitle = prompt(\""._PNFORUM_BBCODE_ENTER_SITE_TITLE."\", \""._PNFORUM_BBCODE_ENTER_PAGE_TITLE."\");\n";
    $out.= "var urlBBCode = \"[URL=\"+thisURL+\"]\"+thisTitle+\"[/URL]\";\n";
    $out.= "revisedMessage = currentMessage+urlBBCode;\n";
    $out.= "post.message.value=revisedMessage;\n";
    $out.= "post.message.focus();\n";
    $out.= "return;\n";
    $out.= "}\n";
    $out.= "\n";
    $out.= "if (action == \"email\") {\n";
    $out.= "var thisEmail = prompt(\""._PNFORUM_BBCODE_ENTER_EMAIL_ADDRESS."\", \"\");\n";
    $out.= "var emailBBCode = \"[EMAIL]\"+thisEmail+\"[/EMAIL]\";\n";
    $out.= "revisedMessage = currentMessage+emailBBCode;\n";
    $out.= "post.message.value=revisedMessage;\n";
    $out.= "post.message.focus();\n";
    $out.= "return;\n";
    $out.= "}\n";
    $out.= "\n";
    $out.= "if (action == \"bold\") {\n";
    $out.= "var thisBold = prompt(\""._PNFORUM_BBCODE_ENTER_TEXT_BOLD."\", \"\");\n";
    $out.= "var boldBBCode = \"[B]\"+thisBold+\"[/B]\";\n";
    $out.= "revisedMessage = currentMessage+boldBBCode;\n";
    $out.= "post.message.value=revisedMessage;\n";
    $out.= "post.message.focus();\n";
    $out.= "return;\n";
    $out.= "}\n";
    $out.= "\n";
    $out.= "if (action == \"italic\") {\n";
    $out.= "var thisItal = prompt(\""._PNFORUM_BBCODE_ENTER_TEXT_ITALIC."\", \"\");\n";
    $out.= "var italBBCode = \"[I]\"+thisItal+\"[/I]\";\n";
    $out.= "revisedMessage = currentMessage+italBBCode;\n";
    $out.= "post.message.value=revisedMessage;\n";
    $out.= "post.message.focus();\n";
    $out.= "return;\n";
    $out.= "}\n";
    $out.= "\n";
    $out.= "if (action == \"underline\") {\n";
    $out.= "var thisUL = prompt(\""._PNFORUM_BBCODE_ENTER_TEXT_UNDERLINE."\", \"\");\n";
    $out.= "var ulBBCode = \"[u]\"+thisUL+\"[/u]\";\n";
    $out.= "revisedMessage = currentMessage+ulBBCode;\n";
    $out.= "post.message.value=revisedMessage;\n";
    $out.= "post.message.focus();\n";
    $out.= "return;\n";
    $out.= "}\n";
    $out.= "\n";
    $out.= "if (action == \"image\") {\n";
    $out.= "var thisImage = prompt(\""._PNFORUM_BBCODE_ENTER_WEBIMAGE_URL."\", \"http://\");\n";
    $out.= "var imageBBCode = \"[IMG]\"+thisImage+\"[/IMG]\";\n";
    $out.= "revisedMessage = currentMessage+imageBBCode;\n";
    $out.= "post.message.value=revisedMessage;\n";
    $out.= "post.message.focus();\n";
    $out.= "return;\n";
    $out.= "}\n";
    $out.= "\n";
    $out.= "if (action == \"quote\") {\n";
    $out.= "var quoteBBCode = \"[QUOTE]  [/QUOTE]\";\n";
    $out.= "revisedMessage = currentMessage+quoteBBCode;\n";
    $out.= "post.message.value=revisedMessage;\n";
    $out.= "post.message.focus();\n";
    $out.= "return;\n";
    $out.= "}\n";
    $out.= "\n";
    $out.= "if (action == \"code\") {\n";
    $out.= "var codeBBCode = \"[CODE]  [/CODE]\";\n";
    $out.= "revisedMessage = currentMessage+codeBBCode;\n";
    $out.= "post.message.value=revisedMessage;\n";
    $out.= "post.message.focus();\n";
    $out.= "return;\n";
    $out.= "}\n";
    $out.= "\n";
    $out.= "if (action == \"listopen\") {\n";
    $out.= "var liststartBBCode = \"[LIST]\";\n";
    $out.= "revisedMessage = currentMessage+liststartBBCode;\n";
    $out.= "post.message.value=revisedMessage;\n";
    $out.= "post.message.focus();\n";
    $out.= "return;\n";
    $out.= "}\n";
    $out.= "\n";
    $out.= "if (action == \"listclose\") {\n";
    $out.= "var listendBBCode = \"[/LIST]\";\n";
    $out.= "revisedMessage = currentMessage+listendBBCode;\n";
    $out.= "post.message.value=revisedMessage;\n";
    $out.= "post.message.focus();\n";
    $out.= "return;\n";
    $out.= "}\n";
    $out.= "\n";
    $out.= "if (action == \"listitem\") {\n";
    $out.= "var thisItem = prompt(\""._PNFORUM_BBCODE_ENTER_LIST_ITEM."\", \"\");\n";
    $out.= "var itemBBCode = \"[*]\"+thisItem;\n";
    $out.= "revisedMessage = currentMessage+itemBBCode;\n";
    $out.= "post.message.value=revisedMessage;\n";
    $out.= "post.message.focus();\n";
    $out.= "return;\n";
    $out.= "}\n";
    $out.= "\n";
    $out.= "}\n";
    $out.= "//]]>\n";
    $out.= "</script>\n";
    return $out;

}
?>
