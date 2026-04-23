<?php

return [

    /*
    | Eén bron voor rate limiting op beide entrypoints (web + /api) voor analyze.
    */
    'analyze_throttle' => 'throttle:30,1',

];
