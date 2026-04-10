<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Client;
use App\Models\Employee;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Table;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $pms = PaymentMethod::where('deleted', 0)->get();
        // Solo categorías que tengan productos pertenecientes a la sale_line "RESTAURANTE"
        $pc = Category::where('deleted', 0)->get();
        return view('sales.index');
    }

    public function restaurante()
    {
        //
        $mesas = Table::where('deleted', 0)->get();
        $products = Product::where('deleted', 0)->get();
        $pms = PaymentMethod::where('deleted', 0)->get();
        $employees = Employee::where('deleted', 0)->get();
        $mesa_directa = Table::whereRaw('UPPER(name) = ?', ['DIRECTA'])->where('deleted', 1)->first(); //Mesa directa que no sale en historicos
        // Solo categorías que tengan productos pertenecientes a la sale_line "RESTAURANTE"
        $pc = Category::where('deleted', 0)
            ->whereHas('sale_line', function ($q) {
                $q->where('deleted', 0)
                    ->whereRaw('LOWER(name) = ?', ['Cafetería']);
            })
            ->whereHas('products', function ($q) {
                $q->where('deleted', 0);
            })->get();
        return view('sales.restaurante', compact('pms', 'pc', 'mesas', 'products', 'employees', 'mesa_directa'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $products = Product::where('deleted', 0)->get();
        $employees = Employee::where('deleted', 0)->get();
        $pms = PaymentMethod::where('deleted', 0)->get();
        // Solo categorías que tengan productos pertenecientes a la sale_line "RESTAURANTE"
        $pc = Category::where('deleted', 0)
            ->whereHas('sale_line', function ($q) {
                $q->where('deleted', 0)
                    ->whereRaw('LOWER(name) = ?', ['Ropa']);
            })
            ->whereHas('products', function ($q) {
                $q->where('deleted', 0);
            })->get();
        return view('sales.create', compact('pms', 'pc', 'products', 'employees'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validaciones básicas antes de la transacción
        $validator = Validator::make($request->all(), [
            'type_sale' => 'required|numeric',
            'type_status' => 'required|numeric',
            'voucher_type' => 'required|string|in:Boleta,Factura,Ticket',
            'document'     => 'nullable|numeric',
            'client'       => 'nullable|string',
            'telefono'     => 'nullable|string|max:15',
            'sede_recojo'  => 'nullable|integer|exists:headquarters,id',
            'total'        => 'required|numeric',
            'products'     => 'required',
            'monto'        => 'required|array',
            'fecha_entrega' => 'nullable|date',
            'direccion'    => 'nullable|string',
            'referencia'   => 'nullable|string',
            'observacion'  => 'nullable|string',
            'hora_entrega' => 'nullable|string',
            'employee_id' => 'nullable|int',
            'table_id' => 'nullable|int',
            'status' => 'required|numeric',
        ]);


        // Validaciones condicionales
        $validator->sometimes('document', 'nullable|digits:8', function ($r) {
            return $r->voucher_type === 'Boleta';
        });
        $validator->sometimes('document', 'nullable|digits:11', function ($r) {
            return $r->voucher_type === 'Factura';
        });
        $validator->sometimes('client', 'required|string', function ($r) {
            return $r->voucher_type === 'Factura';
        });
        $validator->sometimes('direccion', 'nullable|string', function ($r) {
            return $r->voucher_type === 'Factura';
        });

        if ($validator->fails()) {
            // Solo log de error para validación fallida
            Log::error('Validación fallida en SaleController@store: ' . $validator->errors()->first());

            return response()->json([
                'status' => false,
                'errors'  => $validator->errors()->messages()
            ], 422);
        }

        try {
            $response = DB::transaction(function () use ($request) {

                $documento = $request->document ?? null;
                $cliente_id = null;
                $cliente_nombre = "varios";
                $foto = $request->file('foto');

                if ($documento) {
                    $clienteEncontrado = Client::where('document', $documento)->first();

                    if ($clienteEncontrado) {
                        $cliente_id = $clienteEncontrado->id;
                        $cliente_nombre = $clienteEncontrado->nombre;
                    } else {
                        $nuevoCliente = Client::create([
                            'document' => $documento,
                            'business_name' => $request->client,
                            'estado' => 0
                        ]);
                        $cliente_id = $nuevoCliente->id;
                        $cliente_nombre = $nuevoCliente->nombre;
                    }
                } else {
                    // Si no hay documento pero el usuario ingresó un nombre, usar ese nombre
                    if ($request->client && trim($request->client) !== '') {
                        $cliente_nombre = $request->client;
                    }
                }

                $type_sale = $request->type_sale ?? null;
                $type_status = $request->type_status ?? null;
                $user_id   = auth()->user()->id; // Usar el usuario autenticado
                $status = $request->status ?? null;
                $fecha_entrega = $request->fecha_entrega ?? null;
                $direccion = $request->direccion ?? null;
                $referencia = $request->referencia ?? null;
                $observacion = $request->observacion ?? null;
                $telefono = $request->telefono ?? null;
                $employee_id = $request->employee_id ?? null;
                $hora_entrega = $request->hora_entrega ?? null;
                $total = floatval($request->total);
                $fecha = now();
                $sede_id = auth()->user()->sede_id ?? null;
                $turno = auth()->user()->shift;
                // Normalizar products: aceptar JSON o inputs con keys tipo products[1][cantidad]
                $rawProducts = $request->input('products');
                if (is_string($rawProducts)) {
                    $products = json_decode($rawProducts, true) ?? [];
                } elseif (is_array($rawProducts)) {
                    // Reindex numeric keys (form inputs often come as associative with numeric keys)
                    $products = array_values($rawProducts);
                } else {
                    $products = [];
                }

                $table_id = $request->table_id;

                //venta directa con mesa null
                if ($table_id){
                    $table = Table::find($table_id);

                    if ($table && strtoupper($table->name) === 'DIRECTA') {
                        $table_id = null;
                    }
                }
                

                // Sanear y unificar claves por cada producto
                $cleanProducts = [];
                foreach ($products as $p) {
                    if (is_array($p) || is_object($p)) {
                        $id = isset($p['id']) ? $p['id'] : (isset($p->id) ? $p->id : null);
                        $cantidad = isset($p['cantidad']) ? $p['cantidad'] : (isset($p->quantity) ? $p->quantity : (isset($p->cantidad) ? $p->cantidad : 0));
                        $precio = isset($p['precio']) ? $p['precio'] : (isset($p->price) ? $p->price : (isset($p->precio) ? $p->precio : 0));
                        if ($id) {
                            $cleanProducts[] = [
                                'id' => $id,
                                'cantidad' => $cantidad,
                                'precio' => $precio,
                            ];
                        }
                    }
                }
                $products = $cleanProducts;

                $venta = Sale::create([
                    'type_sale'      => $type_sale,
                    'type_status'    => $type_status,
                    'user_id'        => $user_id,
                    'voucher_type'   => $request->voucher_type,
                    'total'          => $total,
                    'date'           => $fecha,
                    'client_id'      => $cliente_id,
                    'client_name'    => $cliente_nombre,
                    'phone'          => $telefono,
                    'delivery_hour'  => $hora_entrega,
                    'delivery_date'  => $fecha_entrega,
                    'address'      => $direccion,
                    'reference'      => $referencia,
                    'observation'    => $observacion,
                    'employee_id'    => $employee_id,
                    'table_id'    => $table_id,
                    'shift'    => $turno,
                    'status'         => $status,
                    'deleted'        => 0,
                ]);

                $sale_id = $venta->id;

                if ($foto != null) {
                    $path = $this->guardarFoto($foto, $sale_id);
                }

                foreach ($request->monto as $metodo_id => $monto) {
                    if ($monto !== null && $monto !== '' && floatval($monto) != 0) {
                        Payment::create([
                            'sale_id'           => $venta->id,
                            'payment_method_id' => $metodo_id,
                            'user_id'           => auth()->user()->id,
                            'shift'             => auth()->user()->shift,
                            'date' => now(),
                            'subtotal'          => floatval($monto),
                            'deleted'           => 0,
                        ]);
                    }
                }
                // Guardar detalles de la venta (todos los productos como individuales)
                foreach ($products as $product) {
                    $id = $product['id'];
                    $cantidad = floatval($product['cantidad']);
                    $precio = floatval($product['precio']);
                    $subtotal = $cantidad * $precio;
                    SaleDetail::create([
                        'product_id' => $id,
                        'sale_id'    => $venta->id,
                        'quantity'   => $cantidad,
                        'unit_price' => $precio,
                        'subtotal'   => $subtotal,
                        'estado'     => 0,
                    ]);
                }

                // REDUCIR STOCK: Solo para ventas normales (type_status = 0), no para anticipadas
                if ($type_status == 0) {
                    foreach ($products as $product) {
                        $this->reducirStockProducto($product['id'], floatval($product['cantidad']), $sede_id);
                    }
                }

                // Si es Boleta o Factura, enviamos a SUNAT
                $pdf_url = null;
                $detraction_text = null;
                // En tu método store, después de crear la venta:
                if (in_array($request->voucher_type, ['Boleta', 'Factura'])) {
                    $sunatResponse = $this->sendInvoice($venta);

                    if (!$sunatResponse['status']) {
                        throw new \Exception('Error al enviar a SUNAT: ' . $sunatResponse['console']);
                    }

                    $pdf_url = $sunatResponse['pdf'];
                    $detraction_text = $sunatResponse['detraction_text'];
                } elseif ($request->voucher_type === 'Ticket') {
                    // Generar número correlativo interno para Ticket
                    $numeroInterno = $this->generarNumeroTicket();
                    $venta->update(['number' => $numeroInterno]);

                    // No hay PDF ni texto de detracción para Ticket
                    $pdf_url = null;
                    $detraction_text = null;
                }

                // ...dentro del método store...
                $metodos_pago = [];
                foreach ($request->monto as $metodo_id => $monto) {
                    if ($monto !== null && $monto !== '' && floatval($monto) != 0) {
                        $metodo = PaymentMethod::find($metodo_id);
                        $nombreMetodo = $metodo ? $metodo->nombre : 'Método';
                        $metodos_pago[] = [
                            'nombre' => $nombreMetodo,
                            'monto'  => floatval($monto),
                        ];
                    }
                }

                // Cargar la relación del usuario para la respuesta
                $venta->load('usuario');

                // Respuesta exitosa
                return response()->json([
                    'status'  => true,
                    'message' => 'Venta registrada correctamente.',
                    'sale_id' => $venta->id,
                    'ticket_pdf_url' => route('sales.ticket_pdf', ['sale' => $venta->id]),
                    'venta'   => [
                        'id'            => $venta->id,
                        'user_id'       => $venta->user_id,
                        'usuario'       => $venta->usuario, // Incluir toda la información del usuario
                        'number'        => $venta->number,
                        'cliente'       => $cliente_nombre,
                        'documento'     => $documento ?? '-',
                        'fecha'         => $fecha,
                        'fecha_entrega' => $fecha_entrega ?? '-',
                        'direccion'     => $direccion ?? '-',
                        'productos'     => $products,
                        'total'         => $total,
                        'metodos_pago'  => $metodos_pago, // <-- aquí el array correcto
                        'pagado'        => collect($request->monto)->sum(),
                    ],
                    'pdf'            => $pdf_url,
                    'detraction_text' => $detraction_text,
                ], 201);
            });

            return $response;
        } catch (\Throwable $e) {
            Log::error('❌ Error en store(): ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'error'  => 'Error al registrar venta: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function generarNumeroTicket()
    {
        // Usa transacción para evitar conflictos en concurrencia
        return DB::transaction(function () {
            // Bloquea la fila para actualizar el número
            $registro = DB::table('correlativos')->where('tipo', 'Ticket')->lockForUpdate()->first();

            if (!$registro) {
                // Si no existe registro, crea uno
                DB::table('correlativos')->insert([
                    'tipo' => 'Ticket',
                    'numero' => 1
                ]);
                return 'TICKET-00000001';
            }

            $nuevoNumero = $registro->numero + 1;

            DB::table('correlativos')
                ->where('tipo', 'Ticket')
                ->update(['numero' => $nuevoNumero]);

            // Formatea el número con ceros a la izquierda y prefijo
            return 'TICKET-' . str_pad($nuevoNumero, 8, '0', STR_PAD_LEFT);
        });
    }


    public function sendInvoice(Sale $sale)
    {
        $detraction_text = null;
        $url = config('apisunat.url') . '/personas/lastDocument';
        $personaId = config('apisunat.id');
        $personaToken = config('apisunat.token.prod');

        $catalog = [
            'Boleta' => [
                'InvoiceTypeCode' => '03',
                'PartyIdentification' => '1',
                'serie' => 'B001'
            ],
            'Factura' => [
                'InvoiceTypeCode' => '01',
                'PartyIdentification' => '6',
                'serie' => 'F001'
            ]
        ];

        if (!isset($catalog[$sale->voucher_type])) {
            return [
                'status' => false,
                'console' => 'Tipo de comprobante no soportado para envío a SUNAT.'
            ];
        }

        $cat = $catalog[$sale->voucher_type];

        // Datos del emisor (tu empresa)
        $ruc = config('ruc.number');
        $name = 'LA FINKA SAN IGNACIO E.I.R.L.';
        $address = 'CAL. LAS VIOLETAS NRO. 196  BANCARIOS CHICLAYO CHICLAYO LAMBAYEQUES';

        $client = optional($sale->client);

        $type = $cat['InvoiceTypeCode'];
        $serie = $cat['serie'];

        // Consultar último correlativo SUNAT
        $respUltimo = Http::post($url, [
            'personaId' => $personaId,
            'personaToken' => $personaToken,
            'type' => $type,
            'serie' => $serie
        ]);

        if ($respUltimo->failed()) {
            return [
                'status' => false,
                'console' => 'Error al consultar último correlativo: ' . $respUltimo->body()
            ];
        }

        $responseObj = $respUltimo->object();
        $number = trim($responseObj->suggestedNumber ?? '');

        if (!$number || !is_numeric($number)) {
            return [
                'status' => false,
                'console' => 'No se recibió correlativo válido desde SUNAT.'
            ];
        }

        $number = str_pad($number, 8, "0", STR_PAD_LEFT);

        // Cálculo de montos
        $total = round(floatval($sale->total), 2);
        $subtotal = round($total / 1.18, 2); // IGV 18% en Perú
        $igv = round($total - $subtotal, 2);

        $data = [
            'personaId' => $personaId,
            'personaToken' => $personaToken,
            'fileName' => "{$ruc}-{$type}-{$serie}-{$number}",
            'documentBody' => [
                'cbc:UBLVersionID' => ['_text' => '2.1'],
                'cbc:CustomizationID' => ['_text' => '2.0'],
                'cbc:ID' => ['_text' => "{$serie}-{$number}"],
                'cbc:IssueDate' => [
                    '_text' => now()->format('Y-m-d')
                ],
                'cbc:IssueTime' => [
                    '_text' => now()->format('H:i:s')
                ],
                'cbc:InvoiceTypeCode' => [
                    '_attributes' => ['listID' => '0101'],
                    '_text' => $type
                ],
                'cbc:Note' => [],
                'cbc:DocumentCurrencyCode' => ['_text' => 'PEN'],
                'cac:AccountingSupplierParty' => [
                    'cac:Party' => [
                        'cac:PartyIdentification' => [
                            'cbc:ID' => [
                                '_attributes' => ['schemeID' => '6'],
                                '_text' => $ruc
                            ]
                        ],
                        'cac:PartyLegalEntity' => [
                            'cbc:RegistrationName' => ['_text' => $name],
                            'cac:RegistrationAddress' => [
                                'cbc:AddressTypeCode' => ['_text' => '0000'],
                                'cac:AddressLine' => ['cbc:Line' => ['_text' => $address]]
                            ]
                        ]
                    ]
                ],
                'cac:AccountingCustomerParty' => [
                    'cac:Party' => [
                        'cac:PartyIdentification' => [
                            'cbc:ID' => [
                                '_attributes' => ['schemeID' => $cat['PartyIdentification']],
                                '_text' => $client->document ?? '00000000'
                            ]
                        ],
                        'cac:PartyLegalEntity' => [
                            'cbc:RegistrationName' => ['_text' => $client->business_name ?? 'CLIENTE VARIOS']
                        ]
                    ]
                ],
                'cac:TaxTotal' => [
                    'cbc:TaxAmount' => [
                        '_attributes' => ['currencyID' => 'PEN'],
                        '_text' => $igv
                    ],
                    'cac:TaxSubtotal' => [
                        'cbc:TaxableAmount' => [
                            '_attributes' => ['currencyID' => 'PEN'],
                            '_text' => $subtotal
                        ],
                        'cbc:TaxAmount' => [
                            '_attributes' => ['currencyID' => 'PEN'],
                            '_text' => $igv
                        ],
                        'cac:TaxCategory' => [
                            'cac:TaxScheme' => [
                                'cbc:ID' => ['_text' => '1000'],
                                'cbc:Name' => ['_text' => 'IGV'],
                                'cbc:TaxTypeCode' => ['_text' => 'VAT']
                            ]
                        ]
                    ]
                ],
                'cac:LegalMonetaryTotal' => [
                    'cbc:LineExtensionAmount' => [
                        '_attributes' => ['currencyID' => 'PEN'],
                        '_text' => $subtotal
                    ],
                    'cbc:TaxInclusiveAmount' => [
                        '_attributes' => ['currencyID' => 'PEN'],
                        '_text' => $total
                    ],
                    'cbc:AllowanceTotalAmount' => [],
                    'cbc:PayableAmount' => [
                        '_attributes' => ['currencyID' => 'PEN'],
                        '_text' => $total
                    ]
                ],
                'cac:InvoiceLine' => [],
            ]
        ];

        // Manejo de términos de pago para Facturas
        if ($sale->voucher_type == 'Factura') {
            // Siempre establecer como "Contado"
            $data['documentBody']['cac:PaymentTerms'] = [[
                "cbc:ID" => ["_text" => "FormaPago"],
                "cbc:PaymentMeansID" => ["_text" => "Contado"]
            ]];
        }

        // Detracción para factura > S/700
        // $detraction_text = '';
        // if ($sale->voucher_type == 'Factura' && $total >= 700) {
        //     $detraction = round($total * 0.12, 2);
        //     $detraction_text = "Detracción: Nro. Cta. Banco de la Nación: 00-250-053223, Porcentaje: 12.00, Monto: S/{$detraction}";

        //     $data['documentBody']['cbc:InvoiceTypeCode']['_attributes']['listID'] = '1001';
        //     $data['documentBody']['cbc:Note'][] = [
        //         '_text' => 'OPERACIÓN SUJETA A DETRACCIÓN',
        //         '_attributes' => ['languageLocaleID' => '2006']
        //     ];
        //     $data['documentBody']['cac:PaymentTerms'][] = [
        //         'cbc:ID' => ['_text' => 'Detraccion'],
        //         'cbc:PaymentMeansID' => ['_text' => '022'],
        //         'cbc:PaymentPercent' => ['_text' => '12'],
        //         'cbc:Amount' => [
        //             '_attributes' => ['currencyID' => 'PEN'],
        //             '_text' => $detraction
        //         ]
        //     ];
        //     $data['documentBody']['cac:PaymentMeans'][] = [
        //         'cbc:ID' => ['_text' => 'Detraccion'],
        //         'cbc:PaymentMeansCode' => ['_text' => '001'],
        //         'cac:PayeeFinancialAccount' => [
        //             'cbc:ID' => ['_text' => '00250053223']
        //         ]
        //     ];
        // }

        // Detalle de productos (InvoiceLine) - Adaptado a tu estructura
        $details = $sale->details()->where('unit_price', '>', 0)->get();

        if ($details->isEmpty()) {
            // Si no hay detalles específicos, crear una línea general
            $data['documentBody']['cac:InvoiceLine'][] = [
                'cbc:ID' => ['_text' => 1],
                'cbc:InvoicedQuantity' => [
                    '_attributes' => ['unitCode' => 'NIU'],
                    '_text' => 1
                ],
                'cbc:LineExtensionAmount' => [
                    '_attributes' => ['currencyID' => 'PEN'],
                    '_text' => $subtotal
                ],
                'cac:PricingReference' => [
                    'cac:AlternativeConditionPrice' => [
                        'cbc:PriceAmount' => [
                            '_attributes' => ['currencyID' => 'PEN'],
                            '_text' => $total
                        ],
                        'cbc:PriceTypeCode' => ['_text' => '01']
                    ]
                ],
                'cac:TaxTotal' => [
                    'cbc:TaxAmount' => [
                        '_attributes' => ['currencyID' => 'PEN'],
                        '_text' => $igv
                    ],
                    'cac:TaxSubtotal' => [
                        'cbc:TaxableAmount' => [
                            '_attributes' => ['currencyID' => 'PEN'],
                            '_text' => $subtotal
                        ],
                        'cbc:TaxAmount' => [
                            '_attributes' => ['currencyID' => 'PEN'],
                            '_text' => $igv
                        ],
                        'cac:TaxCategory' => [
                            'cbc:Percent' => ['_text' => 18],
                            'cbc:TaxExemptionReasonCode' => ['_text' => '10'],
                            'cac:TaxScheme' => [
                                'cbc:ID' => ['_text' => '1000'],
                                'cbc:Name' => ['_text' => 'IGV'],
                                'cbc:TaxTypeCode' => ['_text' => 'VAT']
                            ]
                        ]
                    ]
                ],
                'cac:Item' => [
                    'cbc:Description' => ['_text' => 'Venta general']
                ],
                'cac:Price' => [
                    'cbc:PriceAmount' => [
                        '_attributes' => ['currencyID' => 'PEN'],
                        '_text' => $subtotal
                    ]
                ]
            ];
        } else {
            // Usar los detalles específicos de la venta
            $i = 1;
            foreach ($details as $detail) {
                $price = round($detail->unit_price, 2);
                $cost = round($price / 1.18, 2); // Precio sin IGV
                $quantity = $detail->quantity;
                $totalLine = round($price * $quantity, 2);
                $subtotalLine = round($totalLine / 1.18, 2);
                $igvLine = round($totalLine - $subtotalLine, 2);

                $data['documentBody']['cac:InvoiceLine'][] = [
                    'cbc:ID' => ['_text' => $i],
                    'cbc:InvoicedQuantity' => [
                        '_attributes' => ['unitCode' => 'NIU'],
                        '_text' => $quantity
                    ],
                    'cbc:LineExtensionAmount' => [
                        '_attributes' => ['currencyID' => 'PEN'],
                        '_text' => $subtotalLine
                    ],
                    'cac:PricingReference' => [
                        'cac:AlternativeConditionPrice' => [
                            'cbc:PriceAmount' => [
                                '_attributes' => ['currencyID' => 'PEN'],
                                '_text' => $price
                            ],
                            'cbc:PriceTypeCode' => ['_text' => '01']
                        ]
                    ],
                    'cac:TaxTotal' => [
                        'cbc:TaxAmount' => [
                            '_attributes' => ['currencyID' => 'PEN'],
                            '_text' => $igvLine
                        ],
                        'cac:TaxSubtotal' => [
                            [
                                'cbc:TaxableAmount' => [
                                    '_attributes' => ['currencyID' => 'PEN'],
                                    '_text' => $subtotalLine
                                ],
                                'cbc:TaxAmount' => [
                                    '_attributes' => ['currencyID' => 'PEN'],
                                    '_text' => $igvLine
                                ],
                                'cac:TaxCategory' => [
                                    'cbc:Percent' => ['_text' => 18],
                                    'cbc:TaxExemptionReasonCode' => ['_text' => '10'],
                                    'cac:TaxScheme' => [
                                        'cbc:ID' => ['_text' => '1000'],
                                        'cbc:Name' => ['_text' => 'IGV'],
                                        'cbc:TaxTypeCode' => ['_text' => 'VAT']
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'cac:Item' => [
                        'cbc:Description' => ['_text' => optional($detail->product)->name ?? 'Producto']
                    ],
                    'cac:Price' => [
                        'cbc:PriceAmount' => [
                            '_attributes' => ['currencyID' => 'PEN'],
                            '_text' => $cost
                        ]
                    ]
                ];

                $i++;
            }
        }

        // Enviar a SUNAT
        $urlSend = config('apisunat.url') . '/personas/v1/sendBill';
        $source = Http::post($urlSend, $data);
        $response = $source->object();

        if ($source->failed()) {
            return [
                'status' => false,
                'console' => $response->error->message ?? 'Error desconocido al enviar a SUNAT'
            ];
        }

        $documentId = $response->documentId;
        $filename = "{$ruc}-{$type}-{$serie}-{$number}";

        $url = config('apisunat.url') . "/documents/{$documentId}/getPDF/ticket80mm/{$filename}.pdf";

        // Actualizar la venta con los datos de SUNAT
        $sale->update([
            'voucher_id' => $documentId,
            'voucher_file' => $filename . '.pdf',
            'number' => "{$serie}-{$number}"
        ]);

        return [
            'status' => true,
            'pdf' => $url,
            'detraction_text' => $detraction_text
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    public function ticketPdfPreview(Sale $sale)
    {
        $sale->load([
            'details.product',
            'payments.payment_method',
            'client',
            'user'
        ]);

        $itemsCount = $sale->details->count();
        $paymentsCount = $sale->payments->count();
        $extraLines = 0;
        if (!empty($sale->observation)) {
            $extraLines += 2;
        }
        if (!empty($sale->address)) {
            $extraLines += 1;
        }
        if (!empty($sale->reference)) {
            $extraLines += 1;
        }

        // 80mm de ancho en puntos para ticketera; alto ajustado al contenido para evitar espacios en blanco.
        $paperWidth = 226.77;
        $paperHeight = 250 + ($itemsCount * 18) + ($paymentsCount * 14) + ($extraLines * 12);
        $paperHeight = max(300, $paperHeight);

        $pdf = Pdf::loadView('sales.pdf.ticket', [
            'sale' => $sale,
        ])->setPaper([0, 0, $paperWidth, $paperHeight], 'portrait');

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="ticket-venta-' . $sale->id . '.pdf"',
        ]);
    }

    public function ticketRaw(Sale $sale)
    {
        $sale->load([
            'details.product',
            'payments.payment_method',
            'client',
            'user'
        ]);

        $cliente = optional($sale->client)->business_name
            ?: (optional($sale->client)->contact_name ?: ($sale->client_name ?: 'Varios'));
        $documento = optional($sale->client)->document ?: 'N/A';
        $cajero = optional($sale->user)->name ?: (optional($sale->user)->email ?: 'N/A');
        $lineWidth = 42;

        $lines = [];
        $lines[] = "MARARENA";
        $lines[] = "RUC 20606515627";
        $lines[] = str_repeat('-', $lineWidth);
        $lines[] = 'Comprobante: ' . ($sale->voucher_type ?: 'Ticket');
        $lines[] = 'Numero: ' . ($sale->number ?: ('VENTA-' . $sale->id));
        $lines[] = 'Fecha: ' . optional($sale->date)->format('d/m/Y H:i');
        $lines[] = 'Cliente: ' . $this->ticketClip($cliente, $lineWidth - 9);
        $lines[] = 'Doc: ' . $documento;
        $lines[] = 'Cajero: ' . $this->ticketClip($cajero, $lineWidth - 8);
        $lines[] = str_repeat('-', $lineWidth);
        $lines[] = 'CANT  PRODUCTO                 TOTAL';
        $lines[] = str_repeat('-', $lineWidth);

        foreach ($sale->details as $detail) {
            $nombre = $detail->product->name ?? 'Producto';
            $qty = number_format((float) $detail->quantity, 2);
            $total = number_format((float) $detail->subtotal, 2);
            $lines[] = str_pad($qty, 6, ' ', STR_PAD_RIGHT)
                . str_pad($this->ticketClip($nombre, 24), 24, ' ', STR_PAD_RIGHT)
                . str_pad($total, 12, ' ', STR_PAD_LEFT);
        }

        $lines[] = str_repeat('-', $lineWidth);
        $lines[] = str_pad('TOTAL: S/' . number_format((float) $sale->total, 2), $lineWidth, ' ', STR_PAD_LEFT);

        if ($sale->payments->count() > 0) {
            $lines[] = str_repeat('-', $lineWidth);
            $lines[] = 'PAGOS:';
            foreach ($sale->payments as $payment) {
                $metodo = optional($payment->payment_method)->name
                    ?: (optional($payment->payment_method)->nombre ?: 'Metodo');
                $monto = number_format((float) $payment->subtotal, 2);
                $lines[] = str_pad($this->ticketClip($metodo, 28), 28, ' ', STR_PAD_RIGHT)
                    . str_pad('S/' . $monto, 14, ' ', STR_PAD_LEFT);
            }
        }

        if (!empty($sale->observation)) {
            $lines[] = str_repeat('-', $lineWidth);
            $lines[] = 'Obs: ' . $this->ticketClip($sale->observation, $lineWidth - 5);
        }

        $lines[] = str_repeat('-', $lineWidth);
        $lines[] = 'Gracias por su compra';

        return response()->json([
            'status' => true,
            'printer' => config('qz.printer_name', 'Ticketera'),
            'line_width' => $lineWidth,
            'text' => implode("\n", $lines) . "\n",
        ]);
    }

    private function ticketClip(string $text, int $max): string
    {
        $clean = trim(preg_replace('/\s+/', ' ', $text));
        if (strlen($clean) <= $max) {
            return $clean;
        }
        return substr($clean, 0, max(0, $max - 1)) . '.';
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function historic(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $numero_comprobante = $request->input('number');
        $client_name = $request->input('client_name');
        $product_name = $request->input('product_name');
        $client_id = $request->input('client_id');
        $voucher_type = $request->input('voucher_type');
        $payment_method_id = $request->input('payment_method_id');
        $type_sale = $request->input('type_sale');
        $shift = $request->input('shift');

        $client = Client::find($client_id);
        if ($client) {
            // Agrega el nombre al request usando merge
            $request->merge(['client_name' => $client->business_name ? $client->business_name : $client->contact_name]);
        }


        $paymentMethod = PaymentMethod::where('deleted', 0)->get();

        $consulta = Sale::query()
            ->where('deleted', 0)
            ->when($start_date, fn($q) => $q->whereDate('date', '>=', $start_date))
            ->when($end_date, fn($q) => $q->whereDate('date', '<=', $end_date))
            ->when($type_sale, fn($q) => $q->where('type_sale', $type_sale))
            ->when($shift, fn($q) => $q->where('shift', $shift))
            ->when($numero_comprobante, fn($q) => $q->where('number', 'like', "%$numero_comprobante%"))
            ->when($client_id, fn($q) => $q->where('client_id', $client_id))
            ->when($voucher_type, fn($q) => $q->where('voucher_type', $voucher_type))
            ->when($payment_method_id, function ($q) use ($payment_method_id) {
                $q->whereHas('payments', fn($q2) => $q2->where('payment_method_id', $payment_method_id));
            })
            ->when($product_name, function ($q) use ($product_name) {
                $q->where(function($q2) use ($product_name) {
                    $q2->whereHas('details.product', function($q3) use ($product_name) {
                        $q3->where('name', 'like', '%' . $product_name . '%');
                    });
                });
            })
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc');

        $total = $consulta->sum('total');

        $total_pagos = Payment::query()
            ->where('deleted', 0)
            ->when($start_date, fn($q) => $q->whereDate('date', '>=', $start_date))
            ->when($end_date, fn($q) => $q->whereDate('date', '<=', $end_date))
            ->when($payment_method_id, fn($q) => $q->where('payment_method_id', $payment_method_id))
            ->whereHas('sale', function ($q) use ($numero_comprobante, $client_id, $voucher_type, $type_sale) {
                $q->when($numero_comprobante, fn($q2) => $q2->where('number', 'like', "%$numero_comprobante%"))
                ->when($client_id, fn($q2) => $q2->where('client_id',$client_id))
                ->when($type_sale, fn($q) => $q->where('type_sale', $type_sale))
                ->when($voucher_type, fn($q2) => $q2->where('voucher_type', $voucher_type));
            })
            ->sum('subtotal');

        $anticipadas = $consulta->paginate(15);
        $anticipadas->appends($request->all());

        return view('sales.historic', compact(
            'anticipadas', 
            'start_date', 'end_date', 'paymentMethod',
            'voucher_type', 'type_sale', 'shift', 'total', 'total_pagos',
            'payment_method_id'
        ));
    }

    /**
     * Get products by category for AJAX requests
     *
     * @param  int  $categoryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProductsByCategory(Request $request, $categoryId)
    {
        // Opcional: filtrar por sale_line (nombre). Ej: ?sale_line=RESTAURANTE o ?sale_line=ropa
        $saleLine = $request->query('sale_line');

        $query = Product::where('category_id', $categoryId)
            ->where('deleted', 0)
            ->select('id', 'name', 'unit_price', 'quantity');

        $products = $query->get();

        return response()->json($products);
    }

    /**
     * Get all products grouped by category for AJAX requests
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAllProducts(Request $request)
    {
        // Opcional: filtrar por sale_line (nombre). Ej: ?sale_line=RESTAURANTE o ?sale_line=ropa
        $saleLine = $request->query('sale_line');

        $query = Product::with('category')
            ->where('deleted', 0)
            ->select('id', 'name', 'unit_price', 'quantity', 'category_id');

        if ($saleLine) {
            $saleLineLower = strtolower($saleLine);
            $query->whereHas('sale_line', function ($q) use ($saleLineLower) {
                $q->whereRaw('LOWER(name) = ?', [$saleLineLower])->where('deleted', 0);
            });
        }

        $products = $query->get()->groupBy('category_id');

        return response()->json($products);
    }

    /**
     * Guardar foto de la venta
     *
     * @param  \Illuminate\Http\UploadedFile  $foto
     * @param  int  $saleId
     * @return string
     */
    public function guardarFoto($foto, $sale_id)
    {
    $disk = Storage::disk('public');
        $dir = $disk->path('fotos');
        foreach (glob($dir . "/{$sale_id}.*") as $file) {
            @unlink($file);
        }

        $extension = $foto->getClientOriginalExtension();
        $filename = $sale_id . '.' . $extension;
        $path = $foto->storeAs('fotos', $filename, 'public');
        Sale::where('id', $sale_id)->update(['foto' => $path]);
        return $path;
    }

    /**
     * Reducir stock de un producto
     *
     * @param  int  $productId
     * @param  float  $quantity
     * @param  int  $sedeId
     * @return void
     */
    private function reducirStockProducto($productId, $quantity, $sedeId = null)
    {
        try {
            $product = Product::find($productId);
            if ($product) {
                // Reducir el stock general del producto
                $newStock = $product->quantity - $quantity;
                $product->update(['quantity' => max(0, $newStock)]);

                Log::info("Stock reducido para producto ID {$productId}: -{$quantity}. Stock actual: {$newStock}");
            }
        } catch (\Exception $e) {
            Log::error("Error al reducir stock del producto {$productId}: " . $e->getMessage());
        }
    }

    public function consultarSunat(Request $request)
    {
        $doc = $request->query('doc');

        if (!$doc || (strlen($doc) !== 8 && strlen($doc) !== 11)) {
            return response()->json([
                'success' => false,
                'message' => 'Documento inválido'
            ], 422);
        }

        $urlBase = config('apisunat.url');
        $personaId = config('apisunat.id');
        $personaToken = config('apisunat.token.prod');

        try {
            if (strlen($doc) === 8) {
                $url = "$urlBase/personas/$personaId/getDNI?dni=$doc&personaToken=$personaToken";
            } else {
                $url = "$urlBase/personas/$personaId/getRUC?ruc=$doc&personaToken=$personaToken";
            }

            $response = Http::get($url);

            // ✅ LOG TEMPORAL
            Log::info('Consulta a API Sunat/Reniec', [
                'url' => $url,
                'status' => $response->status(),
                'response' => $response->body(),
            ]);

            if ($response->successful()) {
                return response()->json([
                    'success' => true,
                    'data' => $response->json('data')
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo obtener información de SUNAT/RENIEC'
                ], $response->status());
            }
        } catch (\Exception $e) {
            // ✅ LOG ERROR
            Log::error('Error al consultar Sunat', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ], 500);
        }
    }

    public function confirmarPedido(Request $request)
    {
        try {
            $order_id = $request->order_id;
            $order = Order::where('id', $order_id)
                ->firstOrFail();

            $not_confirmed = $order->order_details()
                ->with('product')
                ->where('confirmed', 0) // Solo detalles no confirmados
                ->get();

            //Updatear productos confirmados y orden
            $order->order_details()
                ->where('confirmed', 0)
                ->update(['confirmed' => 1]);

            return response()->json([
                'success' => true,
                'status' => true,
                'table' => $order->table->name,
                'order_id' => $order->id,
                'details' => $not_confirmed->count() > 0 ? $not_confirmed : null
            ]);
        } catch (\Exception $e) {
            Log::error('Error al cerrar mesa: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al confirmar pedidos.']);
        }
    }

    public function precuenta(Request $request)
    {
        try {
            $order_id = $request->order_id;
            $order = Order::where('id', $order_id)
                ->firstOrFail();

            $details = $order->order_details()
                ->with('product')
                ->get();

            $subtotal = $details->sum(function($d) {
                return $d->product_price * $d->quantity;
            });

            return response()->json([
                'success' => true,
                'status' => true,
                'table' => $order->table->name,
                'order_id' => $order->id,
                'subtotal' => $subtotal,
                'details' => $details->count() > 0 ? $details : null
            ]);
        } catch (\Exception $e) {
            Log::error('Error al generar precuenta: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al generar precuenta.']);
        }
    }

    public function abrirMesa($id)
    {
        Log::info('AbrirMesa - Inicio', ['mesa_id' => $id]);
        
        $mesa = Table::with(['order.order_details.product'])->findOrFail($id);
        
        Log::info('AbrirMesa - Mesa encontrada', [
            'mesa' => $mesa->toArray(),
            'has_order' => $mesa->order ? true : false,
            'order_details_count' => $mesa->order && $mesa->order->order_details ? $mesa->order->order_details->count() : 0
        ]);

        if ($mesa->status === 'Libre') {
            $mesa->update([
                'status' => 'Ocupado',
                'opened_at' => now(),
            ]);

            $order = Order::create([
                'table_id' => $mesa->id,
                'status' => 'Abierto'
            ]);
            
            $productos = [];
        } else {
            // Para mesas ocupadas, buscar la orden activa
            $order = $mesa->order;
            
            // Si no existe orden, crear una nueva (caso edge)
            if (!$order) {
                $order = Order::create([
                    'table_id' => $mesa->id,
                    'status' => 'Abierto'
                ]);
                $productos = [];
            } else {
                // Cargar productos existentes si hay una orden
                $productos = [];
                if ($order->order_details && $order->order_details->count() > 0) {
                    Log::info('OrderDetails encontrados', [
                        'count' => $order->order_details->count(),
                        'detalles' => $order->order_details->toArray()
                    ]);

                    $productos = $order->order_details->map(function ($detalle) {
                        Log::info('Procesando detalle', [
                            'detalle_raw' => $detalle->toArray(),
                            'product' => $detalle->product ? $detalle->product->toArray() : null
                        ]);

                        // Usar nombres exactos de la base de datos
                        $nombre = ($detalle->product_id == 238)
                            ? 'Producto Personalizado'  // Para casos especiales, usar nombre genérico
                            : ($detalle->product ? $detalle->product->name : 'Producto');

                        $producto_mapeado = [
                            'id'         => $detalle->product_id,
                            'nombre'     => $nombre,
                            'cantidad'   => $detalle->quantity,        // Campo exacto de la DB
                            'precio'     => $detalle->product_price,   // Campo exacto de la DB
                            'confirmado' => $detalle->confirmed,       // Campo exacto de la DB
                            'stock'      => $detalle->product ? $detalle->product->quantity : 9999
                        ];

                        Log::info('Producto mapeado', $producto_mapeado);
                        return $producto_mapeado;
                    })->toArray();
                }
            }
        }

        Log::info('AbrirMesa - Respuesta', [
            'mesa_id' => $mesa->id,
            'order_id' => $order->id,
            'productos_count' => count($productos),
            'productos' => $productos
        ]);

        return response()->json([
            'success' => true,
            'mesa_id' => $mesa->id,
            'opened_at' => $mesa->opened_at,
            'order_id' => $order->id ?? null,
            'productos' => $productos,
            'mesa' => [
                'id' => $mesa->id,
                'name' => $mesa->name,
                'status' => $mesa->status
            ]
        ]);
    }

    public function verPedido($id)
    {
        $mesa = Table::with(['order.order_details.product'])->findOrFail($id);

        if (!$mesa->order) {
            return response()->json([
                'success' => false,
                'message' => 'No hay pedido abierto para esta mesa.'
            ], 404);
        }

        $productos = $mesa->order->order_details->map(function ($detalle) {
            $nombre = ($detalle->product_id == 238)
                ? 'Producto Personalizado'
                : ($detalle->product ? $detalle->product->name : 'Producto');

            return [
                'id'         => $detalle->product_id,
                'nombre'     => $nombre,
                'cantidad'   => $detalle->quantity,      // Corregido
                'precio'     => $detalle->product_price, // Corregido
                'confirmado' => $detalle->confirmed,     // Corregido
                'stock'      => $detalle->product ? $detalle->product->quantity : 9999
            ];
        });

        Log::info('Pedido cargado', [
            'mesa_id' => $id,
            'productos' => $productos
        ]);

        return response()->json([
            'success' => true,
            'order_id' => $mesa->order->id,
            'productos' => $productos
        ]);
    }

    public function cerrarMesa($id)
    {
        try {
            $mesa = Table::with('order.order_details')->findOrFail($id);

            if ($mesa->order) {
                // Eliminar detalles
                $mesa->order->order_details()->delete();

                // Eliminar la orden
                $mesa->order()->delete();
            }

            // Liberar mesa
            $mesa->update([
                'status' => 'libre',
                'opened_at' => null,
            ]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error al cerrar mesa: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al cerrar la mesa.']);
        }
    }

    public function addProductToOrder(Request $request, $orderId)
    {
        try {
            $validated = $request->validate([
                'product_id'      => 'required|integer|exists:products,id',
                'quantity'        => 'required|numeric|min:0',
                'product_price' => 'required|numeric|min:0',
            ]);

            $order = Order::findOrFail($orderId);
            // Usar key simple: order_id + product_id
            $key = [
                'order_id'   => $orderId,
                'product_id' => (int) $validated['product_id'],
            ];

            // Buscar detalle existente
            $detail = OrderDetail::where($key)->first();

            $cantidadNueva  = (float) $validated['quantity'];
            $precioUnitario = (float) $validated['product_price'];
            $sumar          = $request->boolean('sumar'); // true cuando es click en botón

            $nombreOpt = $request->input('nombre', null);

            if ($sumar) {
                // SUMAR cantidad (clicks de botón)
                if ($detail) {
                    $detail->cantidad        = (float) $detail->cantidad + $cantidadNueva;
                    $detail->precio_unitario = $precioUnitario; // actualizar PU si necesario
                    // Actualizar nombre si viene en la request
                    if (!empty($nombreOpt)) {
                        $detail->nombre = $nombreOpt;
                    }
                    $detail->save();
                } else {
                    $detail = OrderDetail::create([
                        'order_id'        => $orderId,
                        'product_id'      => (int) $validated['product_id'],
                        'quantity'        => $cantidadNueva,
                        'product_price' => $precioUnitario,
                    ]);
                }
            } else {
                // SOBREESCRIBIR cantidad (edición desde el input)
                $detail = OrderDetail::updateOrCreate(
                    $key,
                    [
                        'quantity'        => $cantidadNueva,
                        'product_price' => $precioUnitario,
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => $sumar ? 'Cantidad sumada correctamente' : 'Producto actualizado correctamente',
                'data'    => $detail,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error al agregar producto al pedido: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function removeProduct(Request $request, $orderId)
    {
        try {
            $productId = $request->input('product_id');

            OrderDetail::where('order_id', $orderId)
                ->where('product_id', $productId)
                ->delete();

            return response()->json([
                'success' => true,
                'message' => 'Producto eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar producto: ' . $e->getMessage()
            ], 500);
        }
    }

     public function getVoucherData(Request $request){
        try{
            
            $voucher_id = $request->voucher_id;
            $type = $request->type; 

            // cdr solo da en producción! en dev no
            if (!in_array($type,['xml','cdr'])){ //si no es xml ni cdr que lance error
                return response()->json(['status' => false, 'message' => 'Type incorrecto']);
            }

            $response = $this->getInvoiceById($voucher_id);
            $data = $response->getData(true);

            // Manejo de error
            if (isset($data['status']) && $data['status'] === false) {
                return response()->json(['status' => false, 'error' => $data['error'] ?? 'Error desconocido']);
            }

            // Excepción para CDR no disponible
            if ($type === 'cdr' && (empty($data['data']['cdr']) || !filter_var($data['data']['cdr'], FILTER_VALIDATE_URL))) {
                return response()->json([
                    'status' => false,
                    'error' => 'El CDR solo estara disponible cuando el comprobante sea aceptado por SUNAT.'
                ])->header('Content-Type', 'application/json; charset=UTF-8');
            }


           return redirect()->away($data['data'][$type]);

        }catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'error' => 'Error al obtener información del comprobante: ' . $e->getMessage(),
            ], 500);

        }
    }

    
    public function anular(Request $request)
    {
        try {
            $sale_id = $request->sale_id;
            $venta = Sale::findOrFail($sale_id);
    
            if ($venta->deleted !== 0) {
                return response()->json([
                    'status' => false,
                    'error' => 'La venta ya fue anulada anteriormente.'
                ]);
            }
    
            DB::transaction(function () use ($venta) {
                // 2. Cambiar estado en tabla SALES
                $venta->deleted = 1;
                $venta->save();
    
                // 3. Cambiar estado en tabla PAYMENTS asociados a esa venta
                Payment::where('sale_id', $venta->id)
                    ->where('deleted', 0)
                    ->update(['deleted' => 1]);
    
                // 4. Restaurar stock SOLO si la venta redujo stock originalmente
                // Solo las ventas normales (type_status = 0) reducen stock
                if ($venta->type_status == 0) {
                    $detalles = SaleDetail::where('sale_id', $venta->id)->get();
                    foreach ($detalles as $detalle) {
                        $this->restaurarStockProducto(
                            $detalle->product_id,
                            $detalle->quantity
                        );
                    }
                }
            });
    
            return response()->json([
                'status' => true,
                'message' => 'Venta anulada, stock restaurado y pagos desactivados correctamente.'
            ]);
        } catch (\Throwable $e) {
            Log::error("❌ Error al anular venta: " . $e->getMessage());
            return response()->json([
                'status' => false,
                'error' => 'Error inesperado al anular la venta: ' . $e->getMessage()
            ]);
        }
    }

    public function details(Request $request)
    {
        try {
            $sale_id = $request->sale_id;
            
            // Obtener la venta con todas sus relaciones
            $sale = Sale::with([
                'client',
                'details.product',
                'payments.payment_method'
            ])->findOrFail($sale_id);
            
            // Mapear los productos
            $productos = $sale->details->map(function ($detail) {
                return [
                    'id' => $detail->product_id,
                    'nombre' => $detail->product->name,
                    'precio' => round($detail->unit_price, 2),
                    'cantidad' => round($detail->quantity, 2),
                    'subtotal' => round($detail->subtotal, 2),
                ];
            });

            // Mapear los pagos
            $pagos = $sale->payments->map(function ($payment) {
                return [
                    'metodo_pago' => $payment->payment_method->name ?? 'N/A',
                    'monto' => round($payment->subtotal, 2),
                    'fecha' => optional($payment->created_at)->format('d/m/Y H:i'),
                ];
            });

            // Información de la venta
            $ventaInfo = [
                'id' => $sale->id,
                'fecha' => optional($sale->date)->format('d/m/Y H:i:s'),
                'cliente' => $sale->client->business_name ?? $sale->client_name ?? 'Varios',
                'fecha_entrega' => optional($sale->delivery_date)->format('Y-m-d'),
                'hora_entrega' => $sale->delivery_hour,
                'direccion' => $sale->address,
                'referencia' => $sale->reference,
                'observacion' => $sale->observation,
                'total' => round($sale->total, 2),
                'saldo' => round($sale->saldo(), 2),
                'telefono' => $sale->phone,
                'voucher_type' => $sale->voucher_type,
                'number' => $sale->number,
            ];

            // Retorna los detalles en formato JSON
            return response()->json([
                'status' => true,
                'productos' => $productos,
                'pagos' => $pagos,
                'venta' => $ventaInfo,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => 'Error al obtener detalles de venta: ' . $e->getMessage(),
            ], 500);
        }
    }

    
    private function restaurarStockProducto($productId, $cantidadRestaurar)
    {
        try {
            $product = Product::find($productId);
            if ($product) {
                $product->quantity += $cantidadRestaurar;
                $product->save();
                Log::info("Stock restaurado para producto ID {$productId}: +{$cantidadRestaurar}. Stock actual: {$product->quantity}");
            } else {
                Log::warning("Producto ID {$productId} no encontrado al intentar restaurar stock");
            }
        } catch (\Exception $e) {
            Log::error("Error al restaurar stock del producto {$productId}: " . $e->getMessage());
        }
    }

    public function getInvoiceById($id){
        $url = config('apisunat.url') . '/documents/'.$id.'/getById';

        Log::error('url: ' . $url);

        $response = Http::get($url);
        $data = $response->object();
        if ($response->failed()) {
            return response()->json(['status' => false, 'error' => $data->error->message]);
        }

        return response()->json(['status' => true, 'data' => $response->json()]);
    } 

    public function anticipated(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $number = $request->number;
        $client = $request->client;


        // Consulta principal de ventas anticipadas - mostrar todas las anticipadas
        $consulta = Sale::with('client', 'details', 'payments')
            ->where('type_status', 1) //anticipada
            ->where('status', 0) //no entregada, si está entregada pasa al histórico
            ->when($number, function ($query) use ($number) {
                $query->where('number', 'like', '%'.$number.'%');
            })
            ->when($start_date, function ($query) use ($start_date) {
                $query->whereDate('delivery_date', '>=', $start_date);
            })
            ->when($end_date, function ($query) use ($end_date) {
                $query->whereDate('delivery_date', '<=', $end_date);
            })
            ->when($client, function ($query) use ($client) {
                $query->where('client_id', $client);
            })
            ->orderBy('delivery_date', 'desc');

        $anticipadas = $consulta->paginate(15);

        $paymentMethod = PaymentMethod::where('deleted', 0)->get();

        // Obtener productos activos para el modal de edición
        $products = Product::where('deleted',0)
            ->orderBy('name')
            ->get();

        return view('sales.anticipated', compact('anticipadas', 'paymentMethod', 'products'));
    }

    public function delivery(Request $request)
    {
        $start_date = $request->start_date;
        $end_date = $request->end_date;
        $number = $request->number;
        $client = $request->client;


        // Consulta principal de ventas anticipadas - mostrar todas las anticipadas
        $consulta = Sale::with('client', 'details', 'payments')
            ->where('type_status', 2) //delivery
            ->where('status', 0) //no entregada, si está entregada pasa al histórico
            ->when($number, function ($query) use ($number) {
                $query->where('number', 'like', '%'.$number.'%');
            })
            ->when($start_date, function ($query) use ($start_date) {
                $query->whereDate('delivery_date', '>=', $start_date);
            })
            ->when($end_date, function ($query) use ($end_date) {
                $query->whereDate('delivery_date', '<=', $end_date);
            })
            ->when($client, function ($query) use ($client) {
                $query->where('client_id', $client);
            })
            ->orderBy('delivery_date', 'desc');

        $anticipadas = $consulta->paginate(15);

        $paymentMethod = PaymentMethod::where('deleted', 0)->get();

        // Obtener productos activos para el modal de edición
        $products = Product::where('deleted',0)
            ->orderBy('name')
            ->get();

        return view('sales.delivery', compact('anticipadas', 'paymentMethod', 'products'));
    }

    public function generarComprobanteAnticipado(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'tipo_comprobante' => 'required',
            'document' => 'nullable|string|max:11',
            'client' => 'nullable|string|max:255',
            'observacion' => 'nullable|string|max:255'
        ]);

        $sale = Sale::with(['details.product', 'client'])->find($request->sale_id);

        if ($sale->saldo() > 0) {
            return response()->json(['status' => false, 'message' => 'La venta aún tiene saldo pendiente.']);
        }

        if ($sale->numero) {
            return response()->json(['status' => false, 'message' => 'Ya se generó el comprobante.']);
        }

        $client = null;
        if ($request->document) {
            $client = Client::where('document', $request->document)->first();

            if ($client) {
                if ($request->client && $client->business_name !== $request->client) {
                    $client->business_name = $request->client;
                    $client->save();

                    $sale->client_id = $client->id;
                    $sale->save();
                    $sale->load('client');
                }
            } else {
                // Si no existe, lo crea
                $client = Client::create([
                    'document' => $request->document,
                    'business_name'  => $request->client,
                    'deleted'  => 0
                ]);
            }
        }

        // Asocia el cliente encontrado o creado a la venta
        if ($client) {
            $sale->client_id = $client->id;
        } else if ($request->client) {
            // Si no hay documento pero sí nombre, podrías buscar por nombre exacto,
        }

        // Actualiza la observación si hay cambios
        if ($request->observacion) {
            $sale->observacion = $request->observacion;
        }

        try {
            if ($request->tipo_comprobante === 'ticket') {
                $sale->number = $this->generarNumeroTicket();
                $sale->voucher_type = 'Ticket';
                $sale->save();

                return response()->json([
                    'status' => true,
                    'url_pdf' => $respuesta['pdf'] ?? null,
                    'venta' => $sale,
                    'productos' => $sale->details->map(function ($item) {
                        return [
                            'nombre'   => $item->product->name ?? 'Sin nombre', // ✅ CAMBIO AQUÍ
                            'precio'   => (float) $item->unit_price,
                            'cantidad' => (float) $item->quantity,
                            'subtotal' => (float) $item->subtotal
                        ];
                    })->values(),
                    'tipo_comprobante' => strtolower($sale->voucher_type)
                ]);
            } else {
                $sale->voucher_type = ucfirst($request->tipo_comprobante);
                $sale->save();

                $sale->load('client');
                $respuesta = $this->sendInvoice($sale);


                return response()->json([
                    'status' => true,
                    'url_pdf' => $respuesta['pdf'] ?? null,
                    'venta' => $sale,
                    'productos' => $sale->details->map(function ($item) {
                        return [
                            'nombre'   => $item->product->name ?? 'Sin nombre', // ✅ CAMBIO AQUÍ
                            'precio'   => (float) $item->unit_price,
                            'cantidad' => (float) $item->quantity,
                            'subtotal' => (float) $item->subtotal
                        ];
                    })->values(),

                    'tipo_comprobante' => strtolower($sale->voucher_type)
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error al generar el comprobante: ' . $e->getMessage(),
            ]);
        }
    }

    public function anticipated_print (Request $request)
    {
        try {
            $sale_id = $request->sale_id;
            
            // Obtener la venta con todas sus relaciones
            $sale = Sale::with([
                'client', 
                'user', // Agregar relación con usuario
                'details.product',
                'payments.payment_method'
            ])->findOrFail($sale_id);
            
            // Mapear los productos
            $productos = $sale->details->map(function ($detail) {
                return [
                    'nombre' => $detail->product->name ?? 'Producto',
                    'precio' => round($detail->unit_price, 2),
                    'cantidad' => round($detail->quantity, 2),
                    'subtotal' => round($detail->subtotal, 2),
                ];
            });

            // Mapear los pagos
            $pagos = $sale->payments->map(function ($payment) {
                return [
                    'metodo_pago' => $payment->payment_method->name ?? 'N/A',
                    'monto' => round($payment->subtotal, 2),
                    'fecha' => optional($payment->created_at)->format('d/m/Y H:i'),
                ];
            });

            $tipo = "";
            $type_sale = $sale->type_sale;
            $type_status = $sale->type_status;

            if ($type_sale == 0){
                $tipo = "Punto de venta";
            }else if($type_sale == 1){
                $tipo = "Cafetería";
            }

            if ($type_status == 0){
                $tp = "Directa";
            }else if($type_status == 1){
                $tp = "Anticipada";
            }else if($type_status == 2){
                $tp = "Delivery";
            }

            // Información de la venta
            $ventaInfo = [
                'id' => $sale->id,
                'cliente' => $sale->client->name ?? $sale->client_name ?? 'Varios',
                'document' => $sale->client->document ?? '00000000',
                'tipo' => $tipo,
                'type_sale' => $sale->type_sale,
                'tp' => $tp,
                'fecha' => optional($sale->date)->format('d/m/Y H:i:s'),
                    'fecha_entrega' => optional($sale->delivery_date)->format('Y-m-d'),
                'direccion' => $sale->address,
                'referencia' => $sale->reference,
                'observacion' => $sale->observation,
                'total' => round($sale->total, 2),
                'saldo' => round($sale->saldo(), 2),
                'telefono' => $sale->phone,
                'user_id' => $sale->user->email ?? 'No especificado', // Usar solo email del usuario
                'voucher_type' => $sale->voucher_type,
                'number' => $sale->number,
                'ticket_number' => $sale->ticket_number,
                'hora_entrega' => $sale->delivery_time,
            ];

            return response()->json([
                'status' => true,
                'productos' => $productos,
                'pagos' => $pagos,
                'venta' => $ventaInfo,
                'now' => now()->format('d/m/Y H:i:s'),
                'user' => ['name' => Auth::user()->email ?? '-'],
            ]);
            
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => 'Error al obtener datos para impresión: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function subirFoto(Request $request)
    {
        try {
            $sale_id = $request->sale_id;
            $request->validate([
                'foto' => 'required|mimes:jpg,jpeg,png,webp|max:4096',
            ]);

            $foto = $request->file('foto');

            $path = $this->guardarFoto($foto, $sale_id);

            return response()->json(['path' => $path, 'success' => true]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'error' => 'Error al guardar foto: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function registrarPago(Request $request)
    {
        $request->validate([
            'sale_id' => 'required|exists:sales,id',
            'monto' => 'required|numeric|min:0.01',
            'metodo' => 'required|exists:payment_methods,name',
        ]);

        try {
            DB::beginTransaction();

            $venta = Sale::findOrFail($request->sale_id);
            $montoPagado = Payment::where('sale_id', $venta->id)->sum('subtotal');
            $saldoPendiente = $venta->total - $montoPagado;

            if ($saldoPendiente <= 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Esta venta ya está completamente pagada.'
                ], 400);
            }

            $montoPago = floatval($request->monto);
            if ($montoPago > $saldoPendiente) {
                return response()->json([
                    'status' => false,
                    'message' => 'El monto ingresado excede el saldo pendiente.'
                ], 422);
            }

            $metodo = PaymentMethod::whereRaw('UPPER(name) = ?', [strtoupper($request->metodo)])->first();
            if (!$metodo) {
                return response()->json([
                    'status' => false,
                    'message' => 'Método de pago no válido.'
                ], 422);
            }

            Payment::create([
                'sale_id' => $venta->id,
                'payment_method_id' => $metodo->id,
                'subtotal' => $montoPago,
                'date' => now(),
                'deleted' => 0,
                'user_id' => auth()->user()->id,
                'shift' => auth()->user()->shift,
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Pago registrado correctamente.',
                'nuevo_saldo' => $venta->total - ($montoPagado + $montoPago)
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('❌ Error en registrarPago(): ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'Error al registrar el pago.'
            ], 500);
        }
    }

    public function updateDetails(Request $request)
    {
        try {
            // Si productos viene como JSON string, decodificarlo
            $productos = $request->productos;
            if (is_string($productos)) {
                $productos = json_decode($productos, true);
            }

            // Debug: Log de los datos recibidos
            Log::info('Datos recibidos en updateDetails:', [
                'productos' => $productos,
                'sale_id' => $request->sale_id,
                'telefono' => $request->telefono,
                'fecha_entrega' => $request->fecha_entrega,
                'hora_entrega' => $request->hora_entrega,
                'total' => $request->total,
                'total_type' => gettype($request->total),
                'total_empty' => empty($request->total),
                'has_foto' => $request->hasFile('foto'),
                'all_request' => $request->all()
            ]);

            // Crear una nueva instancia de request con productos decodificados
            $requestData = $request->all();
            $requestData['productos'] = $productos;
            $request->merge($requestData);

            DB::beginTransaction();

            $sale = Sale::findOrFail($request->sale_id);

            // Manejar la foto si se proporciona
            if ($request->hasFile('foto')) {
                $foto = $request->file('foto');
                $this->guardarFoto($foto, $sale->id);
            }

            // Actualizar los campos de la venta
            $sale->update([
                'phone' => $request->telefono,
                'delivery_date' => $request->fecha_entrega,
                'delivery_hour' => $request->hora_entrega,
                'address' => $request->direccion,
                'reference' => $request->referencia,
                'observation' => $request->observacion,
                'total' => (float) $request->total
            ]);

            // Eliminar detalles existentes
            $sale->details()->delete();

            // Crear nuevos detalles
            foreach ($productos as $producto) {
                $sale->details()->create([
                    'product_id' => $producto['id'],
                    'unit_price' => $producto['precio'],
                    'quantity' => $producto['cantidad'],
                    'subtotal' => $producto['precio'] * $producto['cantidad']
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Detalles actualizados correctamente',
                'sale' => $sale
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar los detalles: ' . $e->getMessage()
            ], 500);
        }
    }

     public function confirmarEntrega($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $sale = Sale::with('details')->find($id);

                if (!$sale) {
                    return response()->json(['status' => false, 'message' => 'Venta no encontrada']);
                }

                // Verificar que sea una venta anticipada (type_sale = 1) y que no esté ya entregada
                // if ($sale->type_sale == 1 && $sale->status == 1) {
                //     // Reducir stock al confirmar la entrega de venta anticipada
                //     foreach ($sale->details as $detail) {
                //         $this->reducirStockProducto($detail->product_id, $detail->quantity, $sale->headquarter_id);
                //     }
                // }

                $sale->status = 1; // Marcar como entregada
                $sale->save();

                return response()->json(['status' => true, 'message' => 'Entrega confirmada']);
            });
        } catch (\Exception $e) {
            Log::error('Error en confirmarEntrega: ' . $e->getMessage());
            return response()->json([
                'status' => false, 
                'message' => 'Error al confirmar entrega: ' . $e->getMessage()
            ], 500);
        }
    }


    public function deleted(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $numero_comprobante = $request->input('number');
        $client_name = $request->input('client_name');
        $client_id = $request->input('client_id');
        $voucher_type = $request->input('voucher_type');
        $payment_method_id = $request->input('payment_method_id');
        $type_sale = $request->input('type_sale');

        $client = Client::find($client_id);
        if ($client) {
            // Agrega el nombre al request usando merge
            $request->merge(['client_name' => $client->business_name ? $client->business_name : $client->contact_name]);
        }


        $paymentMethod = PaymentMethod::where('deleted', 0)->get();

        $consulta = Sale::query()
            ->where('deleted', 1)
            ->when($start_date, fn($q) => $q->whereDate('date', '>=', $start_date))
            ->when($end_date, fn($q) => $q->whereDate('date', '<=', $end_date))
            ->when($type_sale, fn($q) => $q->where('type_sale', $type_sale))
            ->when($numero_comprobante, fn($q) => $q->where('number', 'like', "%$numero_comprobante%"))
            ->when($client_id, fn($q) => $q->where('client_id', $client_id))
            ->when($voucher_type, fn($q) => $q->where('voucher_type', $voucher_type))
            ->when($payment_method_id, function ($q) use ($payment_method_id) {
                $q->whereHas('payments', fn($q2) => $q2->where('payment_method_id', $payment_method_id));
            })
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc');

        $total = $consulta->sum('total');

        $total_pagos = Payment::query()
            ->where('deleted', 0)
            ->when($start_date, fn($q) => $q->whereDate('date', '>=', $start_date))
            ->when($end_date, fn($q) => $q->whereDate('date', '<=', $end_date))
            ->when($payment_method_id, fn($q) => $q->where('payment_method_id', $payment_method_id))
            ->whereHas('sale', function ($q) use ($numero_comprobante, $client_id, $voucher_type, $type_sale) {
                $q->when($numero_comprobante, fn($q2) => $q2->where('number', 'like', "%$numero_comprobante%"))
                ->when($client_id, fn($q2) => $q2->where('client_id',$client_id))
                ->when($type_sale, fn($q) => $q->where('type_sale', $type_sale))
                ->when($voucher_type, fn($q2) => $q2->where('voucher_type', $voucher_type));
            })
            ->sum('subtotal');

        $anticipadas = $consulta->paginate(15);
        $anticipadas->appends($request->all());

        return view('sales.deleted', compact(
            'anticipadas', 
            'start_date', 'end_date', 'paymentMethod',
            'voucher_type', 'type_sale', 'total', 'total_pagos',
            'payment_method_id'
        ));
    }

     public function porProducto(Request $request)
    {
        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $type_sale = $request->input('type_sale');
        $product_id = $request->input('product_id');
        $products = Product::with('category.sale_line')->where('deleted',0)->get();


       $consulta = SaleDetail::select(
            'sale_details.product_id',
            'products.name as product_name',
            DB::raw('SUM(sale_details.quantity) AS total_quantity'),
            DB::raw('SUM(sale_details.subtotal) AS total_amount')
        )
        ->join('products', 'products.id', '=', 'sale_details.product_id')
        ->where('sale_details.deleted', 0)
        ->when($product_id, fn($q) => $q->where('sale_details.product_id', $product_id))
        ->whereHas('sale', function ($q) use ($start_date, $end_date, $type_sale) {
            $q->where('deleted', 0)
                ->when($start_date, fn($q2) => $q2->whereDate('date', '>=', $start_date))
                ->when($end_date,   fn($q2) => $q2->whereDate('date', '<=', $end_date))
                ->when($type_sale != null,  fn($q2) => $q2->where('type_sale', $type_sale));
        })
        ->groupBy('sale_details.product_id', 'products.name')
        ->orderByDesc('total_amount')
        ->get()
        // devolver objetos en lugar de arrays para que en la vista $row->product_name funcione
        ->map(function ($row) {
            return (object) [
                'product_id'     => $row->product_id,
                'product_name'   => $row->product_name ?? 'N/A',
                'total_quantity' => (float) $row->total_quantity,
                'total_amount'   => (float) $row->total_amount,
            ];
        });

        $total = (float) $consulta->sum('total_amount');

        return view('sales.porProducto', compact(
            'products','consulta','total'
        ));
    }

}
