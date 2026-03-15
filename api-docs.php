<?php
require_once __DIR__ . '/includes/auth.php';
require_login();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Docs — Simplx CMS</title>
    <link rel="stylesheet" href="styles.css">
    
</head>
<body>
<div class="container">

    <!-- Sidebar -->
    <?php include __DIR__ . '/includes/nav.php'; ?>

    <!-- Main content -->
    <div class="main-content">

        <div class="page-header">
            <h1>Simplx CMS — API Docs</h1>
            <p>API reference for the Simplx CMS ↔ RAM WMS integration — IDM 250</p>
        </div>

        <!-- Base URL -->
        <div id="base-url" class="base-url">
            <div class="label">CMS Base URL</div>
            <code>https://digmstudents.westphal.drexel.edu/~lg845/simplx/api/v1</code>
            <div class="label" style="margin-top:16px;">WMS Base URL</div>
            <code>https://digmstudents.westphal.drexel.edu/~rt656/ram/api/v1</code>
        </div>

        <!-- Authentication -->
        <h2 id="auth" class="section-title">Authentication</h2>
        <div class="auth-block">
            <p>All requests — both inbound and outbound — require an API key passed in the request header. Each system has its own key.</p>
            <table class="param-table">
                <thead>
                    <tr><th>Header</th><th>Required</th><th>Value</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>x-api-key</code></td>
                        <td><span class="required">Required</span></td>
                        <td>For requests <strong>to CMS</strong>: <code>simplx-key-2026</code><br>For requests <strong>to WMS</strong>: <code>ram-key-2026</code></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Workflow -->
        <h2 id="workflow" class="section-title">Workflow</h2>
        <div class="flow">
            <div class="flow-steps">
                <div class="flow-step">
                    <div class="step-num">1</div>
                    <div class="step-text">CMS creates a SKU → automatically POSTs to WMS <code>/skus.php</code> to sync the product catalog.</div>
                </div>
                <div class="flow-step">
                    <div class="step-num">2</div>
                    <div class="step-text">CMS creates an MPL (manifest packing list) with internal inventory units → sends it to WMS <code>/mpls.php</code>.</div>
                </div>
                <div class="flow-step">
                    <div class="step-num">3</div>
                    <div class="step-text">WMS confirms the MPL → POSTs <code>action: confirm</code> to CMS <code>/mpls.php</code>. CMS moves units from internal → warehouse inventory.</div>
                </div>
                <div class="flow-step">
                    <div class="step-num">4</div>
                    <div class="step-text">CMS creates an order from warehouse units → sends it to WMS <code>/orders.php</code>.</div>
                </div>
                <div class="flow-step">
                    <div class="step-num">5</div>
                    <div class="step-text">WMS ships the order → POSTs <code>action: ship</code> to CMS <code>/orders.php</code>. CMS records shipped items and removes units from inventory.</div>
                </div>
            </div>
        </div>

        <hr>

        <!-- ── INBOUND ── -->
        <h2 class="section-title">Inbound Endpoints (WMS → CMS)</h2>

        <!-- MPL Confirm -->
        <div id="mpls-confirm" class="endpoint">
            <div class="endpoint-header">
                <span class="method method-post">POST</span>
                <span class="endpoint-path">/api/v1/mpls.php</span>
            </div>
            <div class="endpoint-body">
                <h3>Confirm MPL</h3>
                <p>Called by the WMS to confirm a previously received MPL. On success, CMS updates the MPL status to <code>confirmed</code> and moves all associated inventory units from <em>internal</em> to <em>warehouse</em>.</p>

                <div class="sub-label">Request Body</div>
                <table class="param-table">
                    <thead>
                        <tr><th>Field</th><th>Type</th><th>Required</th><th>Description</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>action</code></td>
                            <td>string</td>
                            <td><span class="required">Required</span></td>
                            <td>Must be <code>"confirm"</code></td>
                        </tr>
                        <tr>
                            <td><code>reference_number</code></td>
                            <td>string</td>
                            <td><span class="required">Required</span></td>
                            <td>The MPL reference number (e.g. <code>MPL-2026-001</code>)</td>
                        </tr>
                    </tbody>
                </table>

                <div class="sub-label">Example Call (PHP)</div>
<pre><span class="comment">// WMS confirming an MPL back to CMS</span>
<span class="kw">$url</span>     = <span class="string">'https://digmstudents.westphal.drexel.edu/~lg845/simplx/api/v1/mpls.php'</span>;
<span class="kw">$api_key</span> = <span class="string">'simplx-key-2026'</span>;

<span class="kw">$payload</span> = [
    <span class="key">'action'</span>           => <span class="string">'confirm'</span>,
    <span class="key">'reference_number'</span> => <span class="string">'MPL-2026-001'</span>,
];

<span class="kw">$options</span> = [
    <span class="key">'http'</span> => [
        <span class="key">'method'</span>  => <span class="string">'POST'</span>,
        <span class="key">'header'</span>  => <span class="string">"Content-Type: application/json\r\nx-api-key: $api_key\r\n"</span>,
        <span class="key">'content'</span> => json_encode(<span class="kw">$payload</span>),
        <span class="key">'ignore_errors'</span> => <span class="kw">true</span>,
    ]
];

<span class="kw">$context</span>  = stream_context_create(<span class="kw">$options</span>);
<span class="kw">$response</span> = file_get_contents(<span class="kw">$url</span>, false, <span class="kw">$context</span>);
<span class="kw">$result</span>   = json_decode(<span class="kw">$response</span>, true);</pre>

                <div class="sub-label">Response — 200</div>
                <div class="response-label"><span class="status status-200">200 OK</span></div>
<pre>{
    <span class="key">"success"</span>:     <span class="kw">true</span>,
    <span class="key">"message"</span>:     <span class="string">"MPL confirmed"</span>,
    <span class="key">"units_moved"</span>: <span class="number">14</span>
}</pre>

                <div class="sub-label">Error Responses</div>
                <div class="response-label"><span class="status status-400">400</span> Missing or invalid action / reference_number</div>
<pre>{ <span class="key">"error"</span>: <span class="string">"Bad Request"</span>, <span class="key">"details"</span>: <span class="string">"Missing reference_number"</span> }</pre>
                <div class="response-label"><span class="status status-401">401</span> Invalid API key</div>
<pre>{ <span class="key">"error"</span>: <span class="string">"Unauthorized"</span> }</pre>
                <div class="response-label"><span class="status status-404">404</span> MPL not found</div>
<pre>{ <span class="key">"error"</span>: <span class="string">"Not Found"</span>, <span class="key">"details"</span>: <span class="string">"MPL not found"</span> }</pre>
                <div class="response-label"><span class="status status-409">409</span> Already confirmed</div>
<pre>{ <span class="key">"error"</span>: <span class="string">"Conflict"</span>, <span class="key">"details"</span>: <span class="string">"MPL already confirmed"</span> }</pre>
            </div>
        </div>

        <!-- Order Ship -->
        <div id="orders-ship" class="endpoint">
            <div class="endpoint-header">
                <span class="method method-post">POST</span>
                <span class="endpoint-path">/api/v1/orders.php</span>
            </div>
            <div class="endpoint-body">
                <h3>Ship Order</h3>
                <p>Called by the WMS to mark an order as shipped. CMS records all shipped items to the <code>shipped_items</code> table, updates the order status to <code>confirmed</code>, and removes the units from inventory.</p>

                <div class="sub-label">Request Body</div>
                <table class="param-table">
                    <thead>
                        <tr><th>Field</th><th>Type</th><th>Required</th><th>Description</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>action</code></td>
                            <td>string</td>
                            <td><span class="required">Required</span></td>
                            <td>Must be <code>"ship"</code></td>
                        </tr>
                        <tr>
                            <td><code>order_number</code></td>
                            <td>string</td>
                            <td><span class="required">Required</span></td>
                            <td>The order number (e.g. <code>ORD-2026-001</code>)</td>
                        </tr>
                        <tr>
                            <td><code>shipped_at</code></td>
                            <td>string (date)</td>
                            <td><span class="optional">Optional</span></td>
                            <td>Date shipped — format <code>YYYY-MM-DD</code>. Defaults to today.</td>
                        </tr>
                    </tbody>
                </table>

                <div class="sub-label">Example Call (PHP)</div>
<pre><span class="comment">// WMS notifying CMS that an order shipped</span>
<span class="kw">$url</span>     = <span class="string">'https://digmstudents.westphal.drexel.edu/~lg845/simplx/api/v1/orders.php'</span>;
<span class="kw">$api_key</span> = <span class="string">'simplx-key-2026'</span>;

<span class="kw">$payload</span> = [
    <span class="key">'action'</span>       => <span class="string">'ship'</span>,
    <span class="key">'order_number'</span> => <span class="string">'ORD-2026-001'</span>,
    <span class="key">'shipped_at'</span>   => <span class="string">'2026-03-11'</span>,
];

<span class="kw">$options</span> = [
    <span class="key">'http'</span> => [
        <span class="key">'method'</span>  => <span class="string">'POST'</span>,
        <span class="key">'header'</span>  => <span class="string">"Content-Type: application/json\r\nx-api-key: $api_key\r\n"</span>,
        <span class="key">'content'</span> => json_encode(<span class="kw">$payload</span>),
        <span class="key">'ignore_errors'</span> => <span class="kw">true</span>,
    ]
];

<span class="kw">$context</span>  = stream_context_create(<span class="kw">$options</span>);
<span class="kw">$response</span> = file_get_contents(<span class="kw">$url</span>, false, <span class="kw">$context</span>);
<span class="kw">$result</span>   = json_decode(<span class="kw">$response</span>, true);</pre>

                <div class="sub-label">Response — 200</div>
                <div class="response-label"><span class="status status-200">200 OK</span></div>
<pre>{
    <span class="key">"success"</span>:       <span class="kw">true</span>,
    <span class="key">"message"</span>:       <span class="string">"Order marked as shipped"</span>,
    <span class="key">"units_shipped"</span>: <span class="number">6</span>
}</pre>

                <div class="sub-label">Error Responses</div>
                <div class="response-label"><span class="status status-400">400</span> Missing action or order_number</div>
<pre>{ <span class="key">"error"</span>: <span class="string">"Bad Request"</span>, <span class="key">"details"</span>: <span class="string">"Missing order_number"</span> }</pre>
                <div class="response-label"><span class="status status-401">401</span> Invalid API key</div>
<pre>{ <span class="key">"error"</span>: <span class="string">"Unauthorized"</span> }</pre>
                <div class="response-label"><span class="status status-404">404</span> Order not found</div>
<pre>{ <span class="key">"error"</span>: <span class="string">"Not Found"</span>, <span class="key">"details"</span>: <span class="string">"Order not found"</span> }</pre>
                <div class="response-label"><span class="status status-409">409</span> Already shipped</div>
<pre>{ <span class="key">"error"</span>: <span class="string">"Conflict"</span>, <span class="key">"details"</span>: <span class="string">"Order already shipped"</span> }</pre>
            </div>
        </div>

        <hr>

        <!-- ── OUTBOUND ── -->
        <h2 class="section-title">Outbound Calls (CMS → WMS)</h2>

        <!-- SKU Sync -->
        <div id="sku-sync" class="endpoint">
            <div class="endpoint-header">
                <span class="method method-post">POST</span>
                <span class="endpoint-path">WMS /api/v1/skus.php</span>
            </div>
            <div class="endpoint-body">
                <h3>Sync SKU to WMS</h3>
                <p>Automatically called by CMS whenever a SKU is created or edited in <code>sku_management.php</code> or <code>sku_edit.php</code>. If the SKU already exists in the WMS it is updated; otherwise it is created.</p>

                <div class="sub-label">Request Body</div>
                <table class="param-table">
                    <thead>
                        <tr><th>Field</th><th>Type</th><th>Required</th><th>Description</th></tr>
                    </thead>
                    <tbody>
                        <tr><td><code>ficha</code></td><td>int</td><td><span class="optional">Optional</span></td><td>Internal ficha number</td></tr>
                        <tr><td><code>sku</code></td><td>string</td><td><span class="required">Required</span></td><td>SKU code (e.g. <code>1720830-0789</code>)</td></tr>
                        <tr><td><code>description</code></td><td>string</td><td><span class="required">Required</span></td><td>Product description</td></tr>
                        <tr><td><code>uom_primary</code></td><td>string</td><td><span class="optional">Optional</span></td><td><code>BUNDLE</code> or <code>PALLET</code></td></tr>
                        <tr><td><code>piece_count</code></td><td>int</td><td><span class="optional">Optional</span></td><td>Units per bundle/pallet</td></tr>
                        <tr><td><code>length_inches</code></td><td>float</td><td><span class="optional">Optional</span></td><td>Length in inches</td></tr>
                        <tr><td><code>width_inches</code></td><td>float</td><td><span class="optional">Optional</span></td><td>Width in inches</td></tr>
                        <tr><td><code>height_inches</code></td><td>float</td><td><span class="optional">Optional</span></td><td>Height in inches</td></tr>
                        <tr><td><code>weight_lbs</code></td><td>float</td><td><span class="optional">Optional</span></td><td>Weight in lbs</td></tr>
                        <tr><td><code>assembly</code></td><td>string</td><td><span class="optional">Optional</span></td><td><code>"true"</code> or <code>"false"</code></td></tr>
                        <tr><td><code>rate</code></td><td>float</td><td><span class="optional">Optional</span></td><td>Rate</td></tr>
                    </tbody>
                </table>

                <div class="sub-label">Example Call (PHP)</div>
<pre><span class="comment">// From api_client.php — sync_sku_to_wms()</span>
<span class="kw">$env</span> = require __DIR__ . <span class="string">'/.env.php'</span>;

<span class="kw">$sku_data</span> = [
    <span class="key">'ficha'</span>         => <span class="number">445</span>,
    <span class="key">'sku'</span>           => <span class="string">'1720830-0789'</span>,
    <span class="key">'description'</span>   => <span class="string">'ALDER RED SEL 4/4 RGH KD 8-10FT'</span>,
    <span class="key">'uom_primary'</span>   => <span class="string">'BUNDLE'</span>,
    <span class="key">'piece_count'</span>   => <span class="number">140</span>,
    <span class="key">'length_inches'</span> => <span class="number">120.00</span>,
    <span class="key">'width_inches'</span>  => <span class="number">40.00</span>,
    <span class="key">'height_inches'</span> => <span class="number">30.00</span>,
    <span class="key">'weight_lbs'</span>    => <span class="number">2180.55</span>,
    <span class="key">'assembly'</span>      => <span class="string">'false'</span>,
    <span class="key">'rate'</span>          => <span class="number">17.64</span>,
];

<span class="kw">$options</span> = [
    <span class="key">'http'</span> => [
        <span class="key">'method'</span>  => <span class="string">'POST'</span>,
        <span class="key">'header'</span>  => <span class="string">"Content-Type: application/json\r\nx-api-key: "</span> . <span class="kw">$env</span>[<span class="string">'WMS_API_KEY'</span>] . <span class="string">"\r\n"</span>,
        <span class="key">'content'</span> => json_encode(<span class="kw">$sku_data</span>),
        <span class="key">'ignore_errors'</span> => <span class="kw">true</span>,
    ]
];

<span class="kw">$context</span>  = stream_context_create(<span class="kw">$options</span>);
<span class="kw">$response</span> = @file_get_contents(<span class="kw">$env</span>[<span class="string">'WMS_API_URL'</span>] . <span class="string">'/skus.php'</span>, false, <span class="kw">$context</span>);
<span class="kw">$result</span>   = json_decode(<span class="kw">$response</span>, true);</pre>

                <div class="sub-label">Response — SKU Created (201)</div>
                <div class="response-label"><span class="status status-201">201 Created</span></div>
<pre>{ <span class="key">"success"</span>: <span class="kw">true</span>, <span class="key">"id"</span>: <span class="number">31</span> }</pre>

                <div class="sub-label">Response — SKU Updated (200)</div>
                <div class="response-label"><span class="status status-200">200 OK</span></div>
<pre>{ <span class="key">"success"</span>: <span class="kw">true</span>, <span class="key">"message"</span>: <span class="string">"SKU updated"</span> }</pre>
            </div>
        </div>

        <!-- MPL Send -->
        <div id="mpl-send" class="endpoint">
            <div class="endpoint-header">
                <span class="method method-post">POST</span>
                <span class="endpoint-path">WMS /api/v1/mpls.php</span>
            </div>
            <div class="endpoint-body">
                <h3>Send MPL to WMS</h3>
                <p>Called from <code>mpl_records.php</code> when a user clicks "Send to WMS." Each item includes full SKU details so the WMS can auto-create any SKUs it doesn't already have.</p>

                <div class="sub-label">Request Body</div>
                <table class="param-table">
                    <thead>
                        <tr><th>Field</th><th>Type</th><th>Required</th><th>Description</th></tr>
                    </thead>
                    <tbody>
                        <tr><td><code>reference_number</code></td><td>string</td><td><span class="required">Required</span></td><td>MPL reference number</td></tr>
                        <tr><td><code>trailer_number</code></td><td>string</td><td><span class="optional">Optional</span></td><td>Trailer number</td></tr>
                        <tr><td><code>expected_arrival</code></td><td>string (date)</td><td><span class="optional">Optional</span></td><td>Format <code>YYYY-MM-DD</code></td></tr>
                        <tr><td><code>items</code></td><td>array</td><td><span class="required">Required</span></td><td>Array of unit objects (see below)</td></tr>
                        <tr><td><code>items[].unit_id</code></td><td>string</td><td><span class="required">Required</span></td><td>Unit ID (e.g. <code>48114995</code>)</td></tr>
                        <tr><td><code>items[].sku</code></td><td>string</td><td><span class="required">Required</span></td><td>SKU code</td></tr>
                        <tr><td><code>items[].sku_details</code></td><td>object</td><td><span class="required">Required</span></td><td>Full SKU fields (same as SKU sync payload)</td></tr>
                    </tbody>
                </table>

                <div class="sub-label">Example Call (PHP)</div>
<pre><span class="comment">// From api_client.php — send_mpl_to_wms()</span>
<span class="kw">$payload</span> = [
    <span class="key">'reference_number'</span> => <span class="string">'MPL-2026-001'</span>,
    <span class="key">'trailer_number'</span>   => <span class="string">'634477'</span>,
    <span class="key">'expected_arrival'</span> => <span class="string">'2026-03-15'</span>,
    <span class="key">'items'</span>            => [
        [
            <span class="key">'unit_id'</span>     => <span class="string">'48114995'</span>,
            <span class="key">'sku'</span>         => <span class="string">'1720830-0789'</span>,
            <span class="key">'sku_details'</span> => [
                <span class="key">'ficha'</span>         => <span class="number">445</span>,
                <span class="key">'sku'</span>           => <span class="string">'1720830-0789'</span>,
                <span class="key">'description'</span>   => <span class="string">'ALDER RED SEL 4/4 RGH KD 8-10FT'</span>,
                <span class="key">'uom_primary'</span>   => <span class="string">'BUNDLE'</span>,
                <span class="key">'piece_count'</span>   => <span class="number">140</span>,
                <span class="key">'length_inches'</span> => <span class="number">120.00</span>,
                <span class="key">'width_inches'</span>  => <span class="number">40.00</span>,
                <span class="key">'height_inches'</span> => <span class="number">30.00</span>,
                <span class="key">'weight_lbs'</span>    => <span class="number">2180.55</span>,
                <span class="key">'assembly'</span>      => <span class="string">'false'</span>,
                <span class="key">'rate'</span>          => <span class="number">17.64</span>,
            ],
        ],
    ],
];

<span class="kw">$options</span> = [
    <span class="key">'http'</span> => [
        <span class="key">'method'</span>  => <span class="string">'POST'</span>,
        <span class="key">'header'</span>  => <span class="string">"Content-Type: application/json\r\nx-api-key: "</span> . <span class="kw">$env</span>[<span class="string">'WMS_API_KEY'</span>] . <span class="string">"\r\n"</span>,
        <span class="key">'content'</span> => json_encode(<span class="kw">$payload</span>),
        <span class="key">'ignore_errors'</span> => <span class="kw">true</span>,
    ]
];

<span class="kw">$context</span>  = stream_context_create(<span class="kw">$options</span>);
<span class="kw">$response</span> = @file_get_contents(<span class="kw">$env</span>[<span class="string">'WMS_API_URL'</span>] . <span class="string">'/mpls.php'</span>, false, <span class="kw">$context</span>);
<span class="kw">$result</span>   = json_decode(<span class="kw">$response</span>, true);</pre>

                <div class="sub-label">Response — 200</div>
                <div class="response-label"><span class="status status-200">200 OK</span></div>
<pre>{ <span class="key">"success"</span>: <span class="kw">true</span>, <span class="key">"message"</span>: <span class="string">"MPL received"</span>, <span class="key">"mpl_id"</span>: <span class="number">4</span> }</pre>
            </div>
        </div>

        <!-- Order Send -->
        <div id="order-send" class="endpoint">
            <div class="endpoint-header">
                <span class="method method-post">POST</span>
                <span class="endpoint-path">WMS /api/v1/orders.php</span>
            </div>
            <div class="endpoint-body">
                <h3>Send Order to WMS</h3>
                <p>Called from <code>order_records.php</code> when a user clicks "Send to WMS." The WMS verifies that all unit IDs exist in its inventory before accepting. Units must have arrived via a confirmed MPL first.</p>

                <div class="sub-label">Request Body</div>
                <table class="param-table">
                    <thead>
                        <tr><th>Field</th><th>Type</th><th>Required</th><th>Description</th></tr>
                    </thead>
                    <tbody>
                        <tr><td><code>order_number</code></td><td>string</td><td><span class="required">Required</span></td><td>Order number (e.g. <code>ORD-2026-001</code>)</td></tr>
                        <tr><td><code>ship_to_company</code></td><td>string</td><td><span class="required">Required</span></td><td>Recipient company name</td></tr>
                        <tr><td><code>ship_to_street</code></td><td>string</td><td><span class="optional">Optional</span></td><td>Street address</td></tr>
                        <tr><td><code>ship_to_city</code></td><td>string</td><td><span class="optional">Optional</span></td><td>City</td></tr>
                        <tr><td><code>ship_to_state</code></td><td>string</td><td><span class="optional">Optional</span></td><td>2-letter state code</td></tr>
                        <tr><td><code>ship_to_zip</code></td><td>string</td><td><span class="optional">Optional</span></td><td>ZIP code (up to 9 digits)</td></tr>
                        <tr><td><code>items</code></td><td>array</td><td><span class="required">Required</span></td><td>Array of unit objects</td></tr>
                        <tr><td><code>items[].unit_id</code></td><td>string</td><td><span class="required">Required</span></td><td>Unit ID</td></tr>
                    </tbody>
                </table>

                <div class="sub-label">Example Call (PHP)</div>
<pre><span class="comment">// From api_client.php — send_order_to_wms()</span>
<span class="kw">$payload</span> = [
    <span class="key">'order_number'</span>    => <span class="string">'ORD-2026-001'</span>,
    <span class="key">'ship_to_company'</span> => <span class="string">'Simplx INC'</span>,
    <span class="key">'ship_to_street'</span>  => <span class="string">'123 Main St'</span>,
    <span class="key">'ship_to_city'</span>    => <span class="string">'Philadelphia'</span>,
    <span class="key">'ship_to_state'</span>   => <span class="string">'PA'</span>,
    <span class="key">'ship_to_zip'</span>     => <span class="string">'19104'</span>,
    <span class="key">'items'</span>           => [
        [<span class="key">'unit_id'</span> => <span class="string">'48115039'</span>],
        [<span class="key">'unit_id'</span> => <span class="string">'48115040'</span>],
    ],
];

<span class="kw">$options</span> = [
    <span class="key">'http'</span> => [
        <span class="key">'method'</span>  => <span class="string">'POST'</span>,
        <span class="key">'header'</span>  => <span class="string">"Content-Type: application/json\r\nx-api-key: "</span> . <span class="kw">$env</span>[<span class="string">'WMS_API_KEY'</span>] . <span class="string">"\r\n"</span>,
        <span class="key">'content'</span> => json_encode(<span class="kw">$payload</span>),
        <span class="key">'ignore_errors'</span> => <span class="kw">true</span>,
    ]
];

<span class="kw">$context</span>  = stream_context_create(<span class="kw">$options</span>);
<span class="kw">$response</span> = @file_get_contents(<span class="kw">$env</span>[<span class="string">'WMS_API_URL'</span>] . <span class="string">'/orders.php'</span>, false, <span class="kw">$context</span>);
<span class="kw">$result</span>   = json_decode(<span class="kw">$response</span>, true);</pre>

                <div class="sub-label">Response — 200</div>
                <div class="response-label"><span class="status status-200">200 OK</span></div>
<pre>{ <span class="key">"success"</span>: <span class="kw">true</span>, <span class="key">"message"</span>: <span class="string">"Order received"</span>, <span class="key">"order_id"</span>: <span class="number">2</span> }</pre>

                <div class="sub-label">Error Response — Units not in WMS inventory</div>
                <div class="response-label"><span class="status status-400">400</span></div>
<pre>{ <span class="key">"error"</span>: <span class="string">"Bad Request"</span>, <span class="key">"details"</span>: <span class="string">"Units not in WMS inventory: 48115039, 48115040"</span> }</pre>
            </div>
        </div>

    </div>
</div>
</body>
</html>