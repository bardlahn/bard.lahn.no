<?php

// Setting up initial variables

$self_path = __DIR__."/";
$self_name = pathinfo(__FILE__, PATHINFO_FILENAME);
include '_paths.php'; // Fetches paths config

// Running initialisation
include $includes_path . "/init.php";

// Fetching content
include $includes_path . "fetch-main.php";

?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>

<?php

// Printing head elements

include($includes_path."header.php");
include($includes_path."meta.php");
include($includes_path."styles.php");

?>

</head>
<body>

<?php

// Printing body elements

include($includes_path."nav.php");
include($includes_path."body.php");
include($includes_path."footer.php");
include($includes_path."scripts.php");

?>

</body>
</html>