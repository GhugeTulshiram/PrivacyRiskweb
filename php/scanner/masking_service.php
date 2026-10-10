<?php

function maskSensitiveData($text, $detected)
{
    foreach ($detected as $item) {

        $value = $item["value"];
        $type = $item["type"];

        switch ($type) {

            case "Email Address":
                $replacement = "[EMAIL]";
                break;

            case "Phone Number":
                $replacement = "[PHONE]";
                break;

            case "Government ID":
                $replacement = "[GOVERNMENT_ID]";
                break;

            case "Credit Card":
                $replacement = "[CREDIT_CARD]";
                break;

            case "IP Address":
                $replacement = "[IP_ADDRESS]";
                break;

            case "API Key":
                $replacement = "[API_KEY]";
                break;

            case "Password":
                $replacement = "[PASSWORD]";
                break;

            default:
                $replacement = "[SENSITIVE_DATA]";
        }

        $text = str_replace(
            $value,
            $replacement,
            $text
        );
    }

    return $text;
}

?>