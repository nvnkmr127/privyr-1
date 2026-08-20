<?php

namespace Webkul\API\Repositories;

use Webkul\Core\Eloquent\Repository;
use Illuminate\Support\Str;

class ApiCredentialRepository extends Repository
{
    /**
     * Specify Model class name
     *
     * @return mixed
     */
    public function model()
    {
        return 'Webkul\API\Contracts\ApiCredential';
    }
    
    /**
     * Create a new API credential with a generated token.
     * 
     * @param array $data
     * @return array [ApiCredential, string $plainTextToken]
     */
    public function createCredential(array $data)
    {
        $plainTextToken = Str::random(40);
        
        $data['token_hash'] = hash('sha256', $plainTextToken);
        
        $credential = $this->create($data);
        
        return [$credential, $plainTextToken];
    }
}
