<h1>Random salts</h1>

<?php
    for ($i = 1; $i <= 10; $i++) {
        echo unique_md5();
        echo '<br><br>';
    }

    function unique_md5() {
        mt_srand(microtime(true)*100000 + memory_get_usage(true));
        return md5(uniqid(mt_rand(), true));
    }
?>

<h1>Hashing password</h1>

<p>Password: bloempot</p>
<p>Salt: 3a9e96bfbed22c563ca462cbdda4ccc5</p>

<?php
    $password = "bloempot";
    $salt = "3a9e96bfbed22c563ca462cbdda4ccc5";
    $hashedpassword = hash('sha512', $password, $salt);

    echo "<p>Result: <em>" . $hashedpassword . "</em></p>";
?>