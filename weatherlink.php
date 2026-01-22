<?

$k = "eeiuns90ahloj5mc1sijf68yvt5khlte";
$s = "swfp9tpyyhz9hihnzblpcmuas8kfixsv";
$t = time();
$str = "api-key=eeiuns90ahloj5mc1sijf68yvt5khlte&t=" . time();
$sig = hash_hmac("sha256", "api-key=eeiuns90ahloj5mc1sijf68yvt5khlte&t=" . time(), "swfp9tpyyhz9hihnzblpcmuas8kfixsv");
echo "curl \"https://api.weatherlink.com/v2/stations?$str&api-signature=" . hash_hmac("sha256", "api-key=eeiuns90ahloj5mc1sijf68yvt5khlte&t=" . time(), "swfp9tpyyhz9hihnzblpcmuas8kfixsv") . "\"\n";
echo "\n";
$cmd = "curl \"https://api.weatherlink.com/v2/stations?$str&api-signature=$sig\"";
$output = shell_exec($cmd);
