<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'title' => 'Lorem ipsum dolor sit amet consectetur adipiscing',
                'slug' => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing',
                'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec in ex ultricies, consequat tortor eget, imperdiet nunc. Nam eros lectus, ornare in leo sit amet, eleifend vulputate mauris. Nam et interdum ligula, a convallis nisi. Mauris cursus dapibus erat, quis rhoncus augue tempus non. Duis commodo arcu enim, vitae dapibus dui laoreet non. Sed eu nulla et risus hendrerit tristique id a lorem. Sed quis purus tortor. Fusce a sapien ultricies, auctor ligula vitae, varius leo. Integer varius sit amet neque eget consequat. Fusce in libero vel ligula condimentum pretium sit amet ac risus. Donec molestie est turpis, in vestibulum diam gravida vel. Vestibulum pretium pretium nisl, non fringilla turpis efficitur eget. Morbi porttitor blandit euismod. Donec blandit arcu arcu, et dictum massa bibendum vel.

Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae; Proin risus arcu, dignissim eget efficitur quis, congue vel lorem. Cras interdum diam eget bibendum venenatis. Phasellus quam nisl, dictum nec euismod ut, maximus non velit. Aenean maximus nibh pellentesque dapibus sodales. In molestie dictum enim sed imperdiet. Curabitur eu lacus erat. Ut mi ligula, rhoncus in aliquam vel, tristique non felis. Aliquam interdum augue in ante pellentesque suscipit. Duis efficitur eu leo quis tempor.

Phasellus at dui vel tortor tristique egestas a nec diam. Mauris vehicula tellus aliquet mi congue, at vehicula lorem faucibus. Nunc dictum nibh vitae turpis placerat semper. Pellentesque a est at libero euismod facilisis at eu felis. Aenean neque mauris, vehicula quis faucibus quis, pellentesque convallis erat. Nulla non eros pharetra, lobortis risus quis, efficitur est. Nullam quis eleifend purus. Nullam orci augue, euismod ac tempor vitae, congue vitae elit. Sed imperdiet ex at erat interdum viverra. Donec scelerisque orci vitae semper dapibus. Nam varius turpis viverra sem laoreet bibendum. Morbi consectetur pharetra ultricies. Curabitur pellentesque sagittis tellus, tempor bibendum metus rhoncus vel. Fusce nec luctus tellus, ac fermentum massa. Cras bibendum consequat turpis efficitur vestibulum.

In non porttitor arcu, sit amet condimentum enim. Morbi id urna vitae diam placerat venenatis. Aliquam volutpat dictum dignissim. Praesent quis mattis odio, quis rutrum arcu. Pellentesque id diam tincidunt, cursus quam id, vulputate nisl. In malesuada sem at purus fringilla tristique. Suspendisse ut lobortis augue. Ut convallis odio non arcu convallis fringilla. Nulla facilisi.

Donec commodo eros eu sem hendrerit condimentum. Aenean a dui congue, rutrum purus vel, accumsan tellus. In auctor tellus quam, quis laoreet lacus maximus in. Proin eleifend ut lectus ac aliquam. Mauris ultrices sem interdum ex varius dictum. Fusce malesuada finibus ultrices. Integer eget facilisis massa, vel accumsan erat. Proin ac neque quis ex fermentum aliquam id at erat. Proin pharetra euismod ligula, a maximus ipsum mattis volutpat.',
                'is_published' => true,
            ],
        ];

        foreach ($pages as $pageData) {
            Page::create($pageData);
        }
    }
}
