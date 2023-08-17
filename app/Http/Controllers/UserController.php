<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function register(UserRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $user = new User();

            $has_email = User::where('email', '=', $data['email'])->exists();

            // Checking if the request email has already been used
            if ($has_email) {
                DB::rollBack();
                return response()->json([
                    'message' => 'Email em uso'
                ], 500, [], JSON_PRETTY_PRINT);
            }

            $user->fill([...$data, 'password' => bcrypt($data['password'])])->save();

            DB::commit();
            return response()->json([
                'message' => 'Conta criada com sucesso'
            ], 200, [], JSON_PRETTY_PRINT);
        } catch (\Exception $ex) {
            DB::rollBack();
            return response()->json([
                'message' => 'Erro ao criar conta',
                'debug' => [
                    'error' => $ex->getMessage(),
                    'file' => $ex->getFile(),
                    'line' => $ex->getLine()
                ],
            ], 500, [], JSON_PRETTY_PRINT);
        }
    }

    public function login(UserRequest $request)
    {
        try {
            $data = $request->validated();
            $user = User::firstWhere("email", $data["email"]);

            if (!password_verify($data["password"], $user->password))
                return response()->json([
                    'message' => 'E-mail ou senha incorretos'
                ], 401);

            // $token = $user->createToken('token')->plainTextToken;
            $token = $user->createToken('token')->accessToken;

            return response()->json([
                "user" => $user,
                "access_token" => $token,
                "token_type" => "Bearer"
            ], 200);
        } catch (\Exception $ex) {
            return response()->json([
                'message' => 'Erro ao realizar login',
                'debug' => [
                    'error' => $ex->getMessage(),
                    'file' => $ex->getFile(),
                    'line' => $ex->getLine()
                ],
            ], 500, [], JSON_PRETTY_PRINT);
        }
    }

    public function send_user_availability($userId, $language)
    {
        $connection = new AMQPStreamConnection(
            config('queue.connections.rabbitmq.host'),
            config('queue.connections.rabbitmq.port'),
            config('queue.connections.rabbitmq.login'),
            config('queue.connections.rabbitmq.password'),
            config('queue.connections.rabbitmq.vhost')
        );

        $channel = $connection->channel();

        $channel->exchange_declare(
            config('queue.connections.rabbitmq.options.exchange.name'),
            config('queue.connections.rabbitmq.options.exchange.type')
        );

        // Gerar um nome aleatório para a sala
        $roomName = Str::random(8);

        $message = json_encode([
            'user_id' => $userId,
            'language' => $language,
            'room_name' => $roomName,
        ]);

        $messageObj = new AMQPMessage($message);

        // Envie a mensagem contendo informações do usuário e o nome da sala
        $channel->basic_publish(
            $messageObj,
            config('queue.connections.rabbitmq.options.exchange.name'),
            $language
        );

        $channel->close();
        $connection->close();

        return "Sent: {$message}";
    }
}
