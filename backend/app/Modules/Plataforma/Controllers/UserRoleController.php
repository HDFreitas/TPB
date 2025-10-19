<?php

namespace App\Modules\Plataforma\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssignRoleRequest;
use App\Http\Requests\AssignUserRoleRequest;
use App\Services\UserRoleService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class UserRoleController extends Controller
{
    protected $userRoleService;

    public function __construct(UserRoleService $userRoleService)
    {
        $this->userRoleService = $userRoleService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/users/roles/assign",
     *     summary="Atribuir roles a um usuário",
     *     tags={"User Roles"},
     *     security={{"cookieAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "roles"},
     *             @OA\Property(property="user_id", type="integer", example=1, description="ID do usuário"),
     *             @OA\Property(
     *                 property="roles",
     *                 type="array",
     *                 @OA\Items(type="string"),
     *                 example={"Administrador", "Editor"},
     *                 description="Array com os nomes das roles a serem atribuídas"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Roles atribuídas com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Roles atribuídas com sucesso ao usuário."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuário não encontrado",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorResponse")
     *     )
     * )
     * 
     * Atribui roles a um usuário (substitui as roles existentes)
     *
     * @param AssignUserRoleRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignRoles(AssignUserRoleRequest $request)
    {
        try {
            $validated = $request->validated();
            $userId = $validated['user_id'];
            $roles = $validated['roles'];
            
            // Verificar se está tentando atribuir role HUB sem permissão
            $authenticatedUser = Auth::user();
            if (in_array('HUB', $roles) && !$authenticatedUser->can('perfis.gerenciar_hub')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Acesso negado. Apenas usuários com permissão podem atribuir a role HUB.'
                ], Response::HTTP_FORBIDDEN);
            }
            
            $result = $this->userRoleService->assignRolesToUser($userId, $roles);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result
            ], Response::HTTP_OK);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado.'
            ], Response::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/v1/users/roles/remove",
     *     summary="Remover roles de um usuário",
     *     tags={"User Roles"},
     *     security={{"cookieAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"user_id", "roles"},
     *             @OA\Property(property="user_id", type="integer", example=1, description="ID do usuário"),
     *             @OA\Property(
     *                 property="roles",
     *                 type="array",
     *                 @OA\Items(type="string"),
     *                 example={"Editor"},
     *                 description="Array com os nomes das roles a serem removidas"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Roles removidas com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Roles removidas com sucesso do usuário."),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     * 
     * Remove roles específicas de um usuário
     *
     * @param AssignUserRoleRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeRoles(AssignUserRoleRequest $request)
    {
        try {
            $validated = $request->validated();
            $userId = $validated['user_id'];
            $roles = $validated['roles'];
            
            $result = $this->userRoleService->removeRolesFromUser($userId, $roles);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'data' => $result
            ], Response::HTTP_OK);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado.'
            ], Response::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/{userId}/roles",
     *     summary="Obter roles de um usuário",
     *     tags={"User Roles"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="ID do usuário",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Roles do usuário obtidas com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user_id", type="integer", example=1),
     *                 @OA\Property(property="roles", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="permissions", type="array", @OA\Items(type="string"))
     *             )
     *         )
     *     )
     * )
     * 
     * Obtém todas as roles e permissões de um usuário
     *
     * @param int $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserRoles(int $userId)
    {
        try {
            $result = $this->userRoleService->getUserRoles($userId);

            return response()->json([
                'success' => true,
                'data' => $result
            ], Response::HTTP_OK);

        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado.'
            ], Response::HTTP_NOT_FOUND);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/users/roles",
     *     summary="Listar usuários com suas roles",
     *     tags={"User Roles"},
     *     security={{"cookieAuth":{}}},
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Número de itens por página",
     *         @OA\Schema(type="integer", default=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de usuários com roles obtida com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
     * 
     * Lista todos os usuários do tenant com suas respectivas roles
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUsersWithRoles(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 15);
            $users = $this->userRoleService->getUsersWithRoles($perPage);

            return response()->json([
                'success' => true,
                'data' => $users
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        }
    }

}
