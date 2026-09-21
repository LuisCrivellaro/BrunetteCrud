<?php
/**
 * Configuração do login social do painel admin.
 * NÃO compartilhe nem publique este arquivo depois de preencher as chaves.
 *
 * Passo a passo no README ("Login com Google e outras redes").
 */
return [
    // E-mails que podem entrar no admin usando login social.
    // Se estiver vazio, o login social fica bloqueado (segurança).
    'allowed_emails' => [
        // 'voce@gmail.com',
    ],

    // URL pública do site (sem barra no final). Deixe vazio para detectar sozinho.
    // Ex.: 'https://www.meusite.com.br' ou 'http://localhost:8000'
    'base_url' => '',

    // Cole aqui o "Client ID" e o "Client Secret" de cada rede que quiser ativar.
    // Redes sem chave continuam aparecendo, mas avisam que não estão configuradas.
    'providers' => [
        'google'    => ['client_id' => '', 'client_secret' => ''],
        'facebook'  => ['client_id' => '', 'client_secret' => ''],
    ],
];
