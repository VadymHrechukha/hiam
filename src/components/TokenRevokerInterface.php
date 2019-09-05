<?php

namespace hiam\components;

/**
 * Interface TokenRevokerInterface
 *
 * @author Dmytro Naumenko <d.naumenko.a@gmail.com>
 */
interface TokenRevokerInterface
{
    /**
     * Revokes all active tokens that belong to user $userId.
     *
     * @param string $userId
     *
     * @param string|null $clientId oAuth client_id that is authorized to revoke token.
     * When `null`, the issuing oAuth will not be checked.
     *
     * @param string|null $typeHint . MUST be either `access_token`, `refresh_token` or NULL.
     * When `null`, all tokens of user will be removed.
     *
     * @return bool whether any tokens were removed
     */
    public function revokeAllUserTokens(string $userId, ?string $clientId, ?string $typeHint = null): bool;
}