<?php

function calculateRisk($detected)
{
    $score = 0;

    foreach ($detected as $item) {
        $score += $item["risk"];
    }

    if ($score > 100) {
        $score = 100;
    }

    if ($score == 0) {

        $level = "SAFE";

    } elseif ($score <= 20) {

        $level = "LOW";

    } elseif ($score <= 50) {

        $level = "MEDIUM";

    } elseif ($score <= 80) {

        $level = "HIGH";

    } else {

        $level = "CRITICAL";
    }


    switch ($level) {

        case "SAFE":

            $message = "No sensitive information was detected.";

            break;

        case "LOW":

            $message = "Low privacy risk. Some non-critical information was detected.";

            break;

        case "MEDIUM":

            $message = "Moderate privacy risk. Review the detected information before sharing.";

            break;

        case "HIGH":

            $message = "High privacy risk. Sensitive personal or financial information was detected.";

            break;

        case "CRITICAL":

            $message = "Critical privacy risk. Highly sensitive identity, financial, or authentication information was detected.";

            break;

        default:

            $message = "Privacy risk analysis completed.";
    }


    /*
    ==========================================
    SOLUTION / RECOMMENDATIONS
    ==========================================
    */

    $solutions = [];

    if (empty($detected)) {

        $solutions[] =
            "No sensitive information was detected. The text can be shared after normal privacy review.";

    } else {

        foreach ($detected as $item) {

            switch ($item["type"]) {

                case "Aadhaar Number":

                    $solutions[] =
                        "Remove or mask the Aadhaar number before sharing. Use [AADHAAR] instead.";

                    break;

                case "PAN Number":

                    $solutions[] =
                        "Avoid sharing your PAN publicly. Remove it or replace it with [PAN].";

                    break;

                case "Indian Mobile Number":

                    $solutions[] =
                        "Avoid exposing your personal mobile number unnecessarily. Replace it with [MOBILE].";

                    break;

                case "Email Address":

                    $solutions[] =
                        "Use a masked email or placeholder such as [EMAIL] when the real email is not required.";

                    break;

                case "UPI ID":

                    $solutions[] =
                        "Avoid posting payment identifiers publicly. Replace the UPI ID with [UPI].";

                    break;

                case "IFSC Code":

                    $solutions[] =
                        "Share banking information only when required. Replace the IFSC with [IFSC] in public text.";

                    break;

                case "Credit / Debit Card":

                    $solutions[] =
                        "Never share complete card details. Remove the card number or replace it with [CARD].";

                    break;

                case "Password":

                    $solutions[] =
                        "Never share passwords. Remove the password immediately and replace it with [PASSWORD].";

                    break;

                case "API Key":

                    $solutions[] =
                        "Never expose API keys. Revoke exposed keys if necessary and replace the value with [API_KEY].";

                    break;

                case "IP Address":

                    $solutions[] =
                        "Avoid sharing network information when it is not required. Replace it with [IP_ADDRESS].";

                    break;

                case "Indian PIN Code":

                    $solutions[] =
                        "Remove the PIN code if the exact location is not required. Use [PIN_CODE].";

                    break;

                case "GSTIN":

                    $solutions[] =
                        "Share GST information only for legitimate business purposes. Use [GSTIN] in public examples.";

                    break;

                default:

                    $solutions[] =
                        "Review the detected sensitive information and remove unnecessary personal data.";

                    break;
            }
        }
    }


    /*
    ==========================================
    REMOVE DUPLICATE SOLUTIONS
    ==========================================
    */

    $solutions = array_values(
        array_unique($solutions)
    );


    return [

        "score" => $score,

        "level" => $level,

        "message" => $message,

        "solutions" => $solutions

    ];
}

?>