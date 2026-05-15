<?php

namespace App\Constants;

enum UserType: string
{
    case BUSINESS_OWNER = 'business_owner';
    case STAFF = 'staff';
    case CUSTOMER = 'customer';
    case ADMIN = 'admin';
}
