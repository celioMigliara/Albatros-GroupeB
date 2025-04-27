<?php

class Role 
{
    public const ROLE_INVALIDE  = 0;
    public const ADMINISTRATEUR = 1;
    public const TECHNICIEN     = 2;
    public const UTILISATEUR    = 3;
    public const SYSTEME        = 4;

    public static array $RoleToString = 
    [
        self::ROLE_INVALIDE  => 'Role invalide',
        self::ADMINISTRATEUR => 'Administrateur',
        self::TECHNICIEN     => 'Technicien',
        self::UTILISATEUR    => 'Utilisateur',
        self::SYSTEME        => 'Systeme',
    ];

    // Fonction qui retourne le nom du role et qui permet de savoir si le role est valide
    public static function isSameRole($roleId, $roleCompare)
    {
        if (empty($roleId))
        {
            return false;
        }

        return intval($roleId) == $roleCompare;
    }

    // Fonction qui retourne le nom du role et qui permet de savoir si le role est valide
    public static function IsRoleValid($role): bool
    {
        $role = (int) $role;
        return is_integer($role) && $role > self::ROLE_INVALIDE && $role <= self::SYSTEME;
    }
}
