<?php

class Redirect
{

    public static function to($path, $message = null, $content = array())
    {

        // Katsotaan onko $message parametri asetettu
        if (!is_null($message)) {
            // Jos on, lisätään se sessioksi JSON-muodossa
            $_SESSION['flash_message'] = json_encode($message);
        }
        $params = '?';
        foreach ($content as $key => $item) {
            $params .= $key . '=' . $item . '&';
        }
        // Ohjataan käyttäjä annettuun polkuun
        header('Location: ' . BASE_PATH . $path . $params);
        exit();
    }
}
