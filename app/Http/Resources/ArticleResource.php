<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            # Tabla OITM (Artículos)
            'ItemCode' => $this->resource['ItemCode'] ?? null,
            'ItemName' => $this->resource['ItemName'] ?? null,
            'ItemType' => $this->resource['ItemType'] ?? null,
            'ItmsGrpCode' => $this->resource['ItmsGrpCode'] ?? $this->resource['ItemsGroupCode'] ?? null,
            'UgpEntry' => $this->resource['UgpEntry'] ?? $this->resource['UoMGroupEntry'] ?? null,
            'InvntItem' => $this->resource['InventoryItem'] ?? null,
            'SellItem' => $this->resource['SalesItem'] ?? null,
            'PrchseItem' => $this->resource['PurchaseItem'] ?? null,
            'ManageStockByWarehouse' => $this->resource['ManageStockByWarehouse'] ?? null,
            'SWW' => $this->resource['SWW'] ?? null,
            'BuyUnitMsr' => $this->resource['PurchaseUnit'] ?? null,
            'SalUnitMsr' => $this->resource['SalesUnit'] ?? null,
            'PurPackUn' => $this->resource['PurchaseQtyPerPackUnit'] ?? null,

            # Campos adicionales de OITM
            'U_NEGOCIO' => $this->resource['U_NEGOCIO'] ?? null,
            'U_DEPARTAMENTO' => $this->resource['U_DEPARTAMENTO'] ?? null,
            'U_LINEA' => $this->resource['U_LINEA'] ?? null,
            'U_CLASE' => $this->resource['U_CLASE'] ?? null,
            'U_SERIE' => $this->resource['U_SERIE'] ?? null,
            'U_CONTINUIDAD' => $this->resource['U_CONTINUIDAD'] ?? null,
            'U_TEMPORADA' => $this->resource['U_TEMPORADA'] ?? null,
            'U_MARCA' => $this->resource['U_MARCA'] ?? null,
            'U_COMPO' => $this->resource['U_COMPO'] ?? $this->resource['User_Text'] ?? null,
            'U_INTEGRACION' => $this->resource['U_INTEGRACION'] ?? null,
            'U_ANO_CREACION' => $this->resource['U_ANO_CREACION'] ?? null,
            'U_PROCEDENCIA' => $this->resource['U_PROCEDENCIA'] ?? null,

            # Fechas de creación y actualización
            'CreateDate' => $this->resource['CreateDate'] ?? null,
            'UpdateDate' => $this->resource['UpdateDate'] ?? null,

            # Tabla OITW (Inventario por almacén)
            'Inventory' => $this->when(
                isset($this->resource['ItemWarehouseInfoCollection']),
                function () {
                    return collect($this->resource['ItemWarehouseInfoCollection'])->map(function ($warehouse) {
                        return [
                            'WhsCode' => $warehouse['WarehouseCode'] ?? null,
                            'InStock' => (float) ($warehouse['InStock'] ?? 0),
                            'Committed' => (float) ($warehouse['Committed'] ?? 0),
                            'Ordered' => (float) ($warehouse['Ordered'] ?? 0),
                            'MinStock' => (float) ($warehouse['MinStock'] ?? 0),
                            'MaxStock' => (float) ($warehouse['MaxStock'] ?? 0),
                            'Available' => (float) (($warehouse['InStock'] ?? 0) - ($warehouse['Committed'] ?? 0)),
                        ];
                    });
                }
            )
        ];
    }

    /**
     * Mapear valores booleanos de SAP a boolean PHP
     */
    private function mapBoolean($value): ?bool
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            return strtolower($value) === 'tyes' || strtolower($value) === 'y';
        }

        return (bool) $value;
    }

    /**
     * Agregar metadatos adicionales a la respuesta
     */
    public function with($request)
    {
        return [
            'meta' => [
                'timestamp' => now()->toISOString(),
                'version' => '1.0'
            ]
        ];
    }
}
