<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>

<?php

// Printing head elements

include($includes_path."html-head.php");
include($includes_path."css-styles.php");
include($includes_path."html-meta.php");
include($includes_path."scripts-head.php");

?>

</head>
<body>

<?php

// Printing body elements

include($includes_path."html-nav.php");
include($includes_path."html-body.php");
include($includes_path."html-footer.php");
include($includes_path."scripts-body.php");

?>

</body>
</html>