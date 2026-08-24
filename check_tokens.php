<?php
$content = file_get_contents('C:\xampp\htdocs\blissfull\admin\core\app\view\settings-view.php');
$tokens = token_get_all($content);
$if_count = 0;
$elseif_count = 0;
$endif_count = 0;
foreach ($tokens as $token) {
    if (is_array($token)) {
        if ($token[0] === T_IF) $if_count++;
        elseif ($token[0] === T_ELSEIF) $elseif_count++;
        elseif ($token[0] === T_ENDIF) $endif_count++;
    }
}
echo "IF: $if_count, ELSEIF: $elseif_count, ENDIF: $endif_count\n";