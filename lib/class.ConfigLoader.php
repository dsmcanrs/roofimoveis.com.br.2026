<?php
/**
 * ConfigLoader
 * Carrega configurações (tabela `config`) e integrações (tabela `integracao`).
 * Suporta payload único `nome='site'` contendo todos os grupos (recomendado),
 * ou múltiplas linhas `nome='geral'`, `nome='contato'`, etc. (compatibilidade).
 * Extrai apenas a chave `value` de cada campo.
 */
class ConfigLoader {

    private $conn;

    public function __construct($mysqli_conn) {
        $this->conn = $mysqli_conn;
    }

    /**
     * Carrega configs em formato array: $site[grupo][campo] = valor
     * Sempre extrai apenas a chave `value` quando presente; caso contrário retorna string vazia.
     */
    public function loadSiteConfigs() {

        $site = [];

        $sql = "SELECT payload FROM config WHERE nome='site' LIMIT 1";

        $res = $this->conn->query($sql);

        if (!$res) return $site;

        $row = $res->fetch_assoc();

        if (!$row || empty($row['payload'])) return $site;

        $payload = json_decode($row['payload'], true);

        if (!is_array($payload)) return $site;

        foreach ($payload as $grupo => $fields) {

            if (!is_array($fields)) continue;

            foreach ($fields as $index => $value) {
                $site[$grupo][$index] = $value['value'];
            }

        }

        return $site;
    }

    /**
     * Retorna integrações do tipo informado (por exemplo: 'scripts' ou 'site').
     * Filtra apenas integrações ativas conforme payload JSON.
     */
    public function loadSiteIntegrations() {

        $results = [];

        $sql = "SELECT * FROM integracao
                WHERE
                    tipo IN ('site','script')
                AND JSON_EXTRACT(payload, '$.setup.config.fields.ativa.value') = 1";

        $res = $this->conn->query($sql);

        if (!$res) return $results;

        while ($row = $res->fetch_assoc()) {

            if (empty($row['payload'])) continue;

            $payload = json_decode($row['payload'], true);

            if (!is_array($payload)) continue;

            $integration = [];

            // var_export($payload); exit;

            if (isset($payload['setup']['config']['fields'])) {
                foreach ($payload['setup']['config']['fields'] as $index => $field) {
                    if (!is_array($field)) continue;
                    $integration[$index] = $field['value'];
                }
            }

            // push normalized integration
            $results[$row['nome']] = $integration;

        }

        return $results;
    }

}
