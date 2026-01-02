<?php

use OTPHP\TOTP as OTP;

use BaconQrCode\Renderer\ImageRenderer;

use BaconQrCode\Renderer\RendererStyle\RendererStyle;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;

use BaconQrCode\Writer;

class TOTP {

    public static function generateSecret() {

        return OTP::create()->getSecret();

    }

    public static function generateQRCode($secret, $username, $issuer = 'School Management System') {

        $totp = OTP::create($secret);

        $totp->setLabel($username);

        $totp->setIssuer($issuer);

        $uri = $totp->getProvisioningUri();

        $renderer = new ImageRenderer(

            new RendererStyle(400),

            new SvgImageBackEnd()

        );

        $writer = new Writer($renderer);

        return $writer->writeString($uri);

    }

    public static function verify($secret, $code) {

        $totp = OTP::create($secret);

        return $totp->verify($code);

    }

}

