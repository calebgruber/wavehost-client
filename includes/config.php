<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'voxelnodes_waveclient');
define('DB_PASS', 'pLoH,K_POeUz');
define('DB_NAME', 'voxelnodes_waveclient');

// Site configuration
define('SITE_URL', 'https://waveclient.calebgruber.me');
define('SITE_NAME', 'WaveHost');

// OAuth2 configuration for admin
define('OAUTH_CLIENT_ID', 'cXVI6w5oZsO0Crj4qZNP0XVT2stLKKB6si2u0bqX');
define('OAUTH_CLIENT_SECRET', 'KXxloxvACn43DxNaQTMchbamjobr1gHFUL58ML14UXkxyUDwnNOnXVSCCxBPtkmphf9mQUFsSdyJp1EhFNXXheBGOYklhR6RAed2iRkbL8d8DO9kn0mo9klzVwxKpzUM');
define('OAUTH_REDIRECT_URI', SITE_URL . '/admin/oauth_callback.php');
define('OAUTH_PROVIDER_URL', 'https://auth.wavehost.org/application/o/web/');