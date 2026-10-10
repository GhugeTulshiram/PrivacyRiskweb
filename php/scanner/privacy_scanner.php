<?php

function detectSensitiveData($text)
{
    $detected = [];

    /*
    ==========================================
    1. EMAIL ADDRESS
    ==========================================
    */

    if (preg_match_all(
        '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/',
        $text,
        $matches
    )) {
        foreach ($matches[0] as $value) {

            $detected[] = [
                "type" => "Email Address",
                "value" => $value,
                "risk" => 15,
                "level" => "MEDIUM",
                "reason" => "Personal contact information"
            ];
        }
    }


    /*
    ==========================================
    2. INDIAN MOBILE NUMBER
    ==========================================
    */

    if (preg_match_all(
        '/(?<!\d)(?:\+91[\s-]?)?[6-9]\d{9}(?!\d)/',
        $text,
        $matches
    )) {
        foreach ($matches[0] as $value) {

            $detected[] = [
                "type" => "Indian Mobile Number",
                "value" => $value,
                "risk" => 20,
                "level" => "MEDIUM",
                "reason" => "Personal contact information"
            ];
        }
    }


    /*
    ==========================================
    3. AADHAAR NUMBER
    ==========================================
    */

    if (preg_match_all(
        '/(?<!\d)\d{4}[\s-]\d{4}[\s-]\d{4}(?!\d)/',
        $text,
        $matches
    )) {
        foreach ($matches[0] as $value) {

            $detected[] = [
                "type" => "Aadhaar Number",
                "value" => $value,
                "risk" => 35,
                "level" => "HIGH",
                "reason" => "Government-issued identity information"
            ];
        }
    }


    /*
    ==========================================
    4. PAN CARD
    ==========================================
    */

    if (preg_match_all(
        '/\b[A-Z]{5}[0-9]{4}[A-Z]\b/',
        $text,
        $matches
    )) {
        foreach ($matches[0] as $value) {

            $detected[] = [
                "type" => "PAN Number",
                "value" => $value,
                "risk" => 35,
                "level" => "HIGH",
                "reason" => "Tax and financial identity information"
            ];
        }
    }


    /*
    ==========================================
    5. IFSC CODE
    ==========================================
    */

    if (preg_match_all(
        '/\b[A-Z]{4}0[A-Z0-9]{6}\b/',
        $text,
        $matches
    )) {
        foreach ($matches[0] as $value) {

            $detected[] = [
                "type" => "IFSC Code",
                "value" => $value,
                "risk" => 20,
                "level" => "MEDIUM",
                "reason" => "Banking information"
            ];
        }
    }


    /*
    ==========================================
    6. UPI ID
    ==========================================
    */

    if (preg_match_all(
        '/\b[a-zA-Z0-9._-]+@[a-zA-Z0-9._-]+\b/',
        $text,
        $matches
    )) {
        foreach ($matches[0] as $value) {

            if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                continue;
            }

            $detected[] = [
                "type" => "UPI ID",
                "value" => $value,
                "risk" => 20,
                "level" => "MEDIUM",
                "reason" => "Digital payment information"
            ];
        }
    }


    /*
    ==========================================
    7. CREDIT / DEBIT CARD
    ==========================================
    */

    if (preg_match_all(
        '/(?<!\d)(?:\d{4}[\s-]?){3}\d{4}(?!\d)/',
        $text,
        $matches
    )) {
        foreach ($matches[0] as $value) {

            $detected[] = [
                "type" => "Credit / Debit Card",
                "value" => $value,
                "risk" => 40,
                "level" => "HIGH",
                "reason" => "Financial payment information"
            ];
        }
    }


    /*
    ==========================================
    8. IP ADDRESS
    ==========================================
    */

    if (preg_match_all(
        '/(?<!\d)(?:\d{1,3}\.){3}\d{1,3}(?!\d)/',
        $text,
        $matches
    )) {
        foreach ($matches[0] as $value) {

            $detected[] = [
                "type" => "IP Address",
                "value" => $value,
                "risk" => 10,
                "level" => "LOW",
                "reason" => "Network-related information"
            ];
        }
    }


    /*
    ==========================================
    9. API KEY
    ==========================================
    */

    if (preg_match_all(
        '/\b(?:sk-[a-zA-Z0-9_-]{20,}|AIza[a-zA-Z0-9_-]{20,})\b/',
        $text,
        $matches
    )) {
        foreach ($matches[0] as $value) {

            $detected[] = [
                "type" => "API Key",
                "value" => $value,
                "risk" => 40,
                "level" => "HIGH",
                "reason" => "Authentication credential"
            ];
        }
    }


    /*
    ==========================================
    10. PASSWORD
    ==========================================
    */

    if (preg_match_all(
        '/(?:password|passwd|pwd)\s*[:=]\s*[^\s,;]+/i',
        $text,
        $matches
    )) {
        foreach ($matches[0] as $value) {

            $detected[] = [
                "type" => "Password",
                "value" => $value,
                "risk" => 40,
                "level" => "HIGH",
                "reason" => "Authentication credential"
            ];
        }
    }


    /*
    ==========================================
    11. INDIAN PIN CODE
    ==========================================
    */

    if (preg_match_all(
        '/(?<!\d)[1-9][0-9]{5}(?!\d)/',
        $text,
        $matches
    )) {
        foreach ($matches[0] as $value) {

            $detected[] = [
                "type" => "Indian PIN Code",
                "value" => $value,
                "risk" => 8,
                "level" => "LOW",
                "reason" => "Location information"
            ];
        }
    }


    /*
    ==========================================
    12. GSTIN
    ==========================================
    */

    if (preg_match_all(
        '/\b\d{2}[A-Z]{5}\d{4}[A-Z][A-Z0-9]Z[A-Z0-9]\b/',
        $text,
        $matches
    )) {
        foreach ($matches[0] as $value) {

            $detected[] = [
                "type" => "GSTIN",
                "value" => $value,
                "risk" => 25,
                "level" => "MEDIUM",
                "reason" => "Business and tax information"
            ];
        }
    }


    /*
    ==========================================
    RETURN ALL DETECTED DATA
    ==========================================
    */

    return $detected;
}

?>