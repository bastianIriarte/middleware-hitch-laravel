<?php

return [

    /*
    |--------------------------------------------------------------------------
    | POS boletas Configuration
    |--------------------------------------------------------------------------
    |
    | Configuración de tablas para pos
    |
    */

    'TABLES' => [
        'OINV_HEADER' => 'SAP10-OINV-CAB', #DocNum =  1
        'OINV_DETAIL' => 'SAP10-OINV-DET', #ParentKey = 1 que es el mismo Id del DocNum OINV_HEADER
        
        'ORCT_HEADER' => 'SAP10-ORCT-CAB', #DocNum =  1 que es el mismo Id del DocNum OINV_HEADER
        'ORCT_DETAIL' => 'SAP10-ORCT-DET', #ParentKey = 1 que es el mismo Id del DocNum ORCT_HEADER y en esta HAY QUE ACTUALIZAR EL DOCENTRY
        'ORCT_TCR'    => 'SAP10-ORCT-TCR', #CHEQUES NO SE OCUPAN
    ],

];
