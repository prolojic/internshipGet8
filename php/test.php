<?php
$json=array(
    'money' => 222,
    'contacts' =>array('+7(888)-888-88-88','test@test.xx','Sergey')
);
foreach ($json as &$value) {

    if ("$value" === "Array") {
        foreach ($value as &$key) {
            for ($i = 0; $i < strlen("$key"); $i++) {

                if ($key[$i] === '@')
                    $json1['email'][0] = $key;
                else if ($key[$i] === '-')
                    $json1['tel'][0] = $key;
                else $json1['name'][0] = $key;

            }
    }
}
    $json1['money']=$json[money]*38;
}
echo json_encode($json1);
?>