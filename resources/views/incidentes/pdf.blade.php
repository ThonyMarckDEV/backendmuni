<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Detalles del Incidente</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 8px;
        }
        .header {
            background: linear-gradient(to right, #2563eb, #1e40af);
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .section {
            margin-top: 20px;
        }
        .section h2 {
            font-size: 18px;
            border-bottom: 2px solid #bfdbfe;
            padding-bottom: 5px;
            color: #1e40af;
        }
        .detail {
            display: flex;
            align-items: center;
            margin: 10px 0;
        }
        .detail label {
            font-weight: bold;
            width: 150px;
        }
        .detail span {
            flex: 1;
        }
        .badge {
            padding: 5px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        .badge-alta { background-color: #fee2e2; color: #991b1b; }
        .badge-media { background-color: #fef9c3; color: #854d0e; }
        .badge-baja { background-color: #dcfce7; color: #166534; }
        .badge-resuelto { background-color: #dcfce7; color: #166534; }
        .badge-en-progreso { background-color: #fef9c3; color: #854d0e; }
        .badge-pendiente { background-color: #fee2e2; color: #991b1b; }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Detalles del Incidente</h1>
        </div>
        <div class="section">
            <h2>Detalles del Incidente</h2>
            <div class="detail">
                <label>Activo:</label>
                <span>{{ $incidente->activo->codigo_inventario ?? '-' }}</span>
            </div>
            <div class="detail">
                <label>Descripción:</label>
                <span>{{ $incidente->descripcion ?? '-' }}</span>
            </div>
            <div class="detail">
                <label>Prioridad:</label>
                <span class="badge badge-{{ $incidente->prioridad == 2 ? 'alta' : ($incidente->prioridad == 1 ? 'media' : 'baja') }}">
                    {{ $incidente->prioridad == 2 ? 'Alta' : ($incidente->prioridad == 1 ? 'Media' : 'Baja') }}
                </span>
            </div>
        </div>
        <div class="section">
            <h2>Información Adicional</h2>
            <div class="detail">
                <label>Fecha de Reporte:</label>
                <span>{{ $incidente->fecha_reporte ?? '-' }}</span>
            </div>
            <div class="detail">
                <label>Estado:</label>
                <span class="badge badge-{{ $incidente->estado == 2 ? 'resuelto' : ($incidente->estado == 1 ? 'en-progreso' : 'pendiente') }}">
                    {{ $incidente->estado == 2 ? 'Resuelto' : ($incidente->estado == 1 ? 'En progreso' : 'Pendiente') }}
                </span>
            </div>
        </div>
        <div class="footer">
            Generado el {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>
</body>
</html>