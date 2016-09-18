<?php

namespace GraphqlApiBundle\Controller;

use GraphqlApiBundle\Service\GraphqlSchema;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;


class IndexController extends Controller
{
    /**
     * @Route("/graphql/api", name="graphql-api")
     */
    public function indexAction(Request $request)
    {
        /** @var GraphqlSchema $graphqlS */
        $graphqlS = $this->get('graphqlapibundle.service.graphqlschema');
		$result = $graphqlS->execute($request->get('query'));
        return new JsonResponse($result);
    }
}
