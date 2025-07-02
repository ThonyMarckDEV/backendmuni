<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignación de Incidente</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; margin: 0; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; border: 1px solid #ddd; padding: 20px; background: #f9f9f9; }
        h1 { color: #2c3e50; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #3490dc; color: white; }
        .footer { text-align: center; font-size: 12px; color: #777; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Asignación de Incidente</h1>
        <p>Estimado/a Técnico/a,</p>
        <p>Se le ha asignado un nuevo incidente en el sistema. A continuación, se detallan los datos del incidente:</p>

        <table>
            <tr>
                <th>Campo</th>
                <th>Detalle</th>
            </tr>
            <tr>
                <td>ID Incidente</td>
                <td>{{ $incidente['idIncidente'] }}</td>
            </tr>
            <tr>
                <td>Título</td>
                <td>{{ $incidente['titulo'] }}</td>
            </tr>
            <tr>
                <td>Descripción</td>
                <td>{{ $incidente['descripcion'] }}</td>
            </tr>
            <tr>
                <td>Prioridad</td>
                <td>
                    @if ($incidente['prioridad'] == 0)
                        Baja
                    @elseif ($incidente['prioridad'] == 1)
                        Media
                    @elseif ($incidente['prioridad'] == 2)
                        Alta
                    @else
                        Desconocida
                    @endif
                </td>
            </tr>
            <tr>
                <td>Estado</td>
                <td>{{ $incidente['estado'] == 0 ? 'Pendiente' : 'Desconocido' }}</td>
            </tr>
            <tr>
                <td>Fecha de Reporte</td>
                <td>{{ \Carbon\Carbon::parse($incidente['fecha_reporte'])->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td>Activo</td>
                <td>{{ $incidente['activo'] ? $incidente['activo']['codigo_inventario'] . ' (' . $incidente['activo']['tipo'] . ')' : 'No especificado' }}</td>
            </tr>
            <tr>
                <td>Área</td>
                <td>{{ $incidente['area'] ? $incidente['area']['nombre'] : 'Sin Área' }}</td>
            </tr>
            <tr>
                <td>Creado</td>
                <td>{{ \Carbon\Carbon::parse($incidente['created_at'])->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td>Actualizado</td>
                <td>{{ \Carbon\Carbon::parse($incidente['updated_at'])->format('d/m/Y H:i') }}</td>
            </tr>
        </table>

        <p>Por favor, revise el incidente en el sistema y tome las acciones necesarias.</p>

        <div class="footer">
            <p>Gracias,<br>{{ config('app.name') }} MuniGestion2025</p>
            <p>© {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>