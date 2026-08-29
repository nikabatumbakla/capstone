<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class AuthGuard implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
{
    if (!session()->get('isLoggedIn')) {
        $uri = service('uri');
        
        // If they tried to access an admin/staff page, send to /portal
        if (in_array($uri->getSegment(1), ['admin', 'staff'])) {
            return redirect()->to('portal');
        }
        
        // If they tried to access a partner page, send to /partner-gateway
        return redirect()->to('partner-gateway');
    }
}

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing here
    }
}