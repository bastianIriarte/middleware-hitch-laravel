<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\SapServiceLayerService;

class PurchaseOrderResource extends JsonResource
{
    /**
     * Indica si se debe cargar la información de CONTENEDOR.
     *
     * @var bool
     */
    protected $loadContainer = false;

    /**
     * Activa la carga de CONTENEDOR para este recurso.
     *
     * @return $this
     */
    public function withContainer(): self
    {
        $this->loadContainer = true;
        return $this;
    }

    /**
     * Override para collection() que no cargue CONTENEDOR por defecto.
     *
     * @param mixed $resource
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public static function collection($resource)
    {
        return parent::collection($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $data = $this->resource;

        $lines = array_map(function ($line) {
            return [
                'ItemCode'        => $line['ItemCode']        ?? null,
                'Quantity'        => $line['Quantity']        ?? 0,
                'Price'           => $line['Price']           ?? 0,
                'WhsCode'         => $line['WhsCode']         ?? null,
                'OcrCode'         => $line['CostingCode']     ?? '',
                'OcrCode2'        => $line['CostingCode2']    ?? '',
                'OcrCode3'        => $line['CostingCode3']    ?? '',
                'TaxCode'         => $line['TaxCode']         ?? '',
                'U_COMPO_FINAL'   => $line['U_COMPO_FINAL']   ?? '',
                'U_SEI_Aprobador' => $line['U_SEI_Aprobador'] ?? '',
                'U_Integracion'   => $line['U_Integracion']   ?? '',
                'U_Status'        => $line['U_Status']        ?? '',
            ];
        }, $data['DocumentLines'] ?? []);

        $contenedorItems = [];
        if ($this->loadContainer && !empty($data['U_CONTENEDOR'])) {
            $contEntry = (int) $data['U_CONTENEDOR'];
            $sap  = app(SapServiceLayerService::class);
            $resp = $sap->get("/CONTENEDOR({$contEntry})");
            $colec = $resp['response']['F_CONTENEDOR_LINEACollection'] ?? [];

            $contenedorItems = array_map(function ($ctr) use ($resp) {
                return [
                    'U_CONTENEDOR'   => $ctr['U_CONTENEDOR']   ?? null,
                    'U_SELLO'        => $ctr['U_SELLO']        ?? null,
                    'U_DOC_ENTRY_PO' => $ctr['U_DOC_ENTRY_PO'] ?? null,
                    'U_DOC_NUM_PO'   => $resp['response']['Remark'] ?? null,
                ];
            }, $colec);
        }

        $response =  [
            'DocEntry'      => $data['DocEntry']      ?? null,
            'DocNum'        => $data['DocNum']        ?? null,
            'CardCode'      => $data['CardCode']      ?? null,
            'CardName'      => $data['CardName']      ?? null,
            'DocDate'       => $data['DocDate']       ?? null,
            'DocDueDate'    => $data['DocDueDate']    ?? null,
            'NumAtCard'     => $data['NumAtCard']     ?? null,
            'U_INVOICE'     => $data['U_INVOICE']     ?? null,
            'U_BL'          => $data['U_BL']          ?? null,
            'U_ETD'         => $data['U_ETD']         ?? null,
            'U_ETA'         => $data['U_ETA']         ?? null,
            'U_PEMBARQUE'   => $data['U_PEMBARQUE']   ?? null,
            'U_PDESTINO'    => $data['U_PDESTINO']    ?? null,
            'U_PCONSOLID'   => $data['U_PCONSOLID']   ?? null,
            'U_NSALIDA'     => $data['U_NSALIDA']     ?? null,
            'U_NLLEGADA'    => $data['U_NLLEGADA']    ?? null,
            'U_FORWARDER'   => $data['U_FORWARDER']   ?? null,
            'U_AGENCIA'     => $data['U_AGENCIA']     ?? null,
            'U_INTEGRACION' => $data['U_INTEGRACION'] ?? null,
            'lines'         => $lines,
        ];

        if ($this->loadContainer) {
            $response['CONTENEDOR'] = $contenedorItems;
        }

        return $response;
    }
}
