<?php
// Silence is golden — mencegah directory listing pada folder config
http_response_code(403);
exit('Akses ditolak.');
