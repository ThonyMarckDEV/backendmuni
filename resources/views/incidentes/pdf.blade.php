<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Incidente</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Roboto', sans-serif;
            color: #2d3748;
            background-color: #f7fafc;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #3182ce, #2b6cb0);
            color: #ffffff;
            padding: 20px;
            text-align: center;
        }
        .header img {
            max-width: 120px;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
        }
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        .section {
            padding: 20px 30px;
        }
        .section h2 {
            font-size: 20px;
            font-weight: 700;
            color: #2b6cb0;
            border-bottom: 2px solid #bee3f8;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .detail {
            display: flex;
            align-items: flex-start;
            margin: 12px 0;
            font-size: 15px;
        }
        .detail label {
            font-weight: 700;
            width: 160px;
            color: #4a5568;
        }
        .detail span {
            flex: 1;
            color: #2d3748;
            line-height: 1.5;
        }
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .badge-alta {
            background-color: #fed7d7;
            color: #9b2c2c;
        }
        .badge-media {
            background-color: #fefcbf;
            color: #975a16;
        }
        .badge-baja {
            background-color: #c6f6d5;
            color: #276749;
        }
        .badge-resuelto {
            background-color: #c6f6d5;
            color: #276749;
        }
        .badge-en-progreso {
            background-color: #fefcbf;
            color: #975a16;
        }
        .badge-pendiente {
            background-color: #fed7d7;
            color: #9b2c2c;
        }
        .footer {
            background-color: #edf2f7;
            text-align: center;
            padding: 15px;
            font-size: 13px;
            color: #718096;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ public_path('img/logo.png') }}" alt="Logo">
            <h1>Detalles del Incidente</h1>
            <p>ID: {{ $incidente->idIncidente }}</p>
        </div>
        <div class="section">
            <h2>Detalles del Incidente</h2>
            <div class="detail">
                <label>Área:</label>
                <span>{{ $incidente->area->nombre ?? '-' }}</span>
            </div>
            <div class="detail">
                <label>Activo:</label>
                <span>
                    @if($incidente->activo)
                        COD: {{ $incidente->activo->codigo_inventario }} - 
                        TIPO: {{ $incidente->activo->tipo }} - 
                        MARCA: {{ $incidente->activo->marca_modelo }} - 
                        UBICACION: {{ $incidente->activo->ubicacion }}
                    @else
                        -
                    @endif
                </span>
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
                <span>{{ $incidente->fecha_reporte ? \Carbon\Carbon::parse($incidente->fecha_reporte)->format('d/m/Y H:i') : '-' }}</span>
            </div>
            <div class="detail">
                <label>Estado:</label>
                <span class="badge badge-{{ $incidente->estado == 2 ? 'resuelto' : ($incidente->estado == 1 ? 'en-progreso' : 'pendiente') }}">
                    {{ $incidente->estado == 2 ? 'Resuelto' : ($incidente->estado == 1 ? 'En progreso' : 'Pendiente') }}
                </span>
            </div>
        </div>
        <div class="footer">
            Generado el {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }} | Sistema de Gestión de Incidentes
        </div>
    </div>
</body>
</html>
