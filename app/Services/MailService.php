<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use App\Mail\GenericNotification;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class MailService
{
    /**
     * Enviar un correo a los destinatarios especificados usando una plantilla Blade.
     *
     * @param string $template Nombre de la plantilla Blade
     * @param array $data Datos para la plantilla
     * @param array|string $recipients Correos o modelos User
     * @param string $subject Asunto del correo
     * @param string|null $fromEmail Correo del remitente (opcional)
     * @param string|null $fromName Nombre del remitente (opcional)
     * @return bool Estado de éxito
     */
    public function sendEmail($template, array $data, $recipients, string $subject, ?string $fromEmail = null, ?string $fromName = null): bool
    {
        try {
            $recipientEmails = $this->normalizeRecipients($recipients);

            if (empty($recipientEmails)) {
                Log::warning('No se proporcionaron destinatarios válidos para el correo', ['template' => $template]);
                return false;
            }

            foreach ($recipientEmails as $email) {
                Mail::to($email)->send(new GenericNotification($template, $data, $subject, $fromEmail, $fromName));
            }

            Log::info('Correo enviado exitosamente', [
                'template' => $template,
                'recipients' => $recipientEmails,
                'subject' => $subject,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error al enviar correo', [
                'template' => $template,
                'error' => $e->getMessage(),
                'recipients' => $recipientEmails ?? [],
            ]);
            return false;
        }
    }

    /**
     * Enviar correo a todos los usuarios administradores (idRol = 1).
     *
     * @param string $template Nombre de la plantilla Blade
     * @param array $data Datos para la plantilla
     * @param string $subject Asunto del correo
     * @param string|null $fromEmail Correo del remitente (opcional)
     * @param string|null $fromName Nombre del remitente (opcional)
     * @return bool Estado de éxito
     */
    public function sendToAdmins($template, array $data, string $subject, ?string $fromEmail = null, ?string $fromName = null): bool
    {
        $admins = User::where('idRol', 1)
            ->where('estado', 1)
            ->with('datos')
            ->get()
            ->map(function ($user) {
                return $user->datos && $user->datos->email ? $user->datos->email : null;
            })
            ->filter()
            ->values()
            ->toArray();

        Log::debug('Correos de administradores consultados', [
            'admins' => $admins,
            'query' => 'SELECT u.idUsuario, d.email FROM usuarios u JOIN datos d ON u.idDatos = d.idDatos WHERE u.idRol = 1 AND u.estado = 1',
        ]);

        if (empty($admins)) {
            Log::warning('No se encontraron usuarios administradores activos con correos válidos', ['idRol' => 1]);
            return false;
        }

        return $this->sendEmail($template, $data, $admins, $subject, $fromEmail, $fromName);
    }

    /**
     * Enviar correo al técnico asignado (idRol = 3) basado en idTecnico.
     *
     * @param string $template Nombre de la plantilla Blade
     * @param array $data Datos para la plantilla
     * @param int $idTecnico ID del usuario técnico
     * @param string $subject Asunto del correo
     * @param string|null $fromEmail Correo del remitente (opcional)
     * @param string|null $fromName Nombre del remitente (opcional)
     * @return bool Estado de éxito
     */
    public function sendToTechnician($template, array $data, int $idTecnico, string $subject, ?string $fromEmail = null, ?string $fromName = null): bool
    {
        $technician = User::where('idUsuario', $idTecnico)
            ->where('idRol', 3)
            ->where('estado', 1)
            ->with('datos')
            ->first();

        Log::debug('Correo de técnico consultado', [
            'idTecnico' => $idTecnico,
            'email' => $technician && $technician->datos ? $technician->datos->email : null,
            'query' => 'SELECT u.idUsuario, d.email FROM usuarios u JOIN datos d ON u.idDatos = d.idDatos WHERE u.idUsuario = ' . $idTecnico . ' AND u.idRol = 3 AND u.estado = 1',
        ]);

        if (!$technician || !$technician->datos || !$technician->datos->email) {
            Log::warning('No se encontró técnico activo con correo válido', ['idTecnico' => $idTecnico]);
            return false;
        }

        return $this->sendEmail($template, $data, $technician->datos->email, $subject, $fromEmail, $fromName);
    }

    /**
     * Enviar correo al usuario que reportó el incidente (idUsuario).
     *
     * @param string $template Nombre de la plantilla Blade
     * @param array $data Datos para la plantilla
     * @param int $idUsuario ID del usuario que reportó
     * @param string $subject Asunto del correo
     * @param string|null $fromEmail Correo del remitente (opcional)
     * @param string|null $fromName Nombre del remitente (opcional)
     * @return bool Estado de éxito
     */
    public function sendToIncidentReporter($template, array $data, int $idUsuario, string $subject, ?string $fromEmail = null, ?string $fromName = null): bool
    {
        $user = User::where('idUsuario', $idUsuario)
            ->where('estado', 1)
            ->with('datos')
            ->first();

        Log::debug('Correo de usuario reportante consultado', [
            'idUsuario' => $idUsuario,
            'email' => $user && $user->datos ? $user->datos->email : null,
            'query' => 'SELECT u.idUsuario, d.email FROM usuarios u JOIN datos d ON u.idDatos = d.idDatos WHERE u.idUsuario = ' . $idUsuario . ' AND u.estado = 1',
        ]);

        if (!$user || !$user->datos || !$user->datos->email) {
            Log::warning('No se encontró usuario activo con correo válido', ['idUsuario' => $idUsuario]);
            return false;
        }

        return $this->sendEmail($template, $data, $user->datos->email, $subject, $fromEmail, $fromName);
    }

    /**
     * Normalizar destinatarios a una lista de correos.
     *
     * @param array|string $recipients Correos o modelos User
     * @return array Lista de correos
     */
    protected function normalizeRecipients($recipients): array
    {
        if (is_string($recipients)) {
            return [$recipients];
        }

        if (is_array($recipients)) {
            return collect($recipients)
                ->map(function ($recipient) {
                    if (is_object($recipient) && property_exists($recipient, 'datos') && $recipient->datos && property_exists($recipient->datos, 'email')) {
                        return $recipient->datos->email;
                    }
                    if (is_object($recipient) && property_exists($recipient, 'email')) {
                        return $recipient->email;
                    }
                    return is_string($recipient) ? $recipient : null;
                })
                ->filter()
                ->values()
                ->toArray();
        }

        return [];
    }
}