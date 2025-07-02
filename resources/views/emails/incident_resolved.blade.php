<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incidente Resuelto</title>
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
        <h1>Incidente Resuelto</h1>
        <p>Estimado/a {{ $incidente['usuario']['nombre'] }} {{ $incidente['usuario']['apellido'] }},</p>
        <p>Nos complace informarle que el incidente reportado ha sido resuelto. A continuación, los detalles:</p>

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
                <td>Resuelto</td>
            </tr>
            <tr>
                <td>Fecha de Reporte</td>
                <td>{{ \Carbon\Carbon::parse($incidente['fecha_reporte'])->format('d/m/Y H:i') }}</td>
            </tr>
            @if($incidente['activo'])
            <tr>
                <td>Activo</td>
                <td>{{ $incidente['activo']['codigo_inventario'] }} ({{ $incidente['activo']['tipo'] }})</td>
            </tr>
            @endif
            @if($incidente['tecnico'])
            <tr>
                <td>Técnico Asignado</td>
                <td>{{ $incidente['tecnico']['nombre'] }} {{ $incidente['tecnico']['apellido'] }}</td>
            </tr>
            @endif
            @if($incidente['area'])
            <tr>
                <td>Área</td>
                <td>{{ $incidente['area']['nombre'] }}</td>
            </tr>
            @endif
            @if($incidente['comentarios_tecnico'])
            <tr>
                <td>Comentarios del Técnico</td>
                <td>{{ $incidente['comentarios_tecnico'] }}</td>
            </tr>
            @endif
            <tr>
                <td>Creado</td>
                <td>{{ \Carbon\Carbon::parse($incidente['created_at'])->format('d/m/Y H:i') }}</td>
            </tr>
            <tr>
                <td>Actualizado</td>
                <td>{{ \Carbon\Carbon::parse($incidente['updated_at'])->format('d/m/Y H:i') }}</td>
            </tr>
        </table>

        <p>Si tiene alguna pregunta o necesita asistencia adicional, por favor contáctenos.</p>

        <div class="footer">
            <p>Gracias,<br>MuniGestion2025</p>
            <p>© {{ date('Y') }} MuniGestion2025. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>