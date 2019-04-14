<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use App\Entity\User;
use App\Entity\Group;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Swagger\Annotations as SWG;
/**
 * User controller
 * @Route("/api", name="api_")
 */
class UserController extends AbstractFOSRestController
{

    /**
     * Lists all Users
     * @Rest\Get("/users")
     * @return Response
     * @SWG\Response(
     *     response=200,
     *     description="Returns the list of groups"
     * )
     * @Security(name="Bearer")
     */
    public function getUsers()
    {
        $userRepository = $this->getDoctrine()->getRepository(User::class);

        $users = $userRepository->findAll();

        $view = $this->view(compact('users'));
        $view->getContext()->enableMaxDepth();

        return $this->handleView($view);
    }

    /**
     * Create a User
     * @Rest\Post("/users")
     * @param Request $request
     * @param ValidatorInterface $validator
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     * @SWG\Response(
     *     response=201,
     *     description="Returns the created object"
     * )
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="list of parameters to create a user",
     *     required=true,
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(
     *            type="object",
     *            @SWG\Property(property="username", type="string"),
     *            @SWG\Property(property="password", type="string"),
     *         )
     *     )
     * )
     * @Security(name="Bearer")
     */
    public function createUser(Request $request, ValidatorInterface $validator, UserPasswordEncoderInterface $encoder)
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');

        $user = new User();
        $user->setUsername($username);
        $user->setPassword($encoder->encodePassword($user, $password));
        $user->setRole(User::$defaultRole);

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            return $this->handleView($this->view(compact('errors'), 400));
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->handleView($this->view($user, 201));
    }

    /**
     * Delete a user
     * @Rest\Delete("/users/{userId}", requirements={"userId"="\d+"})
     * @param int $userId
     * @return Response
     * @SWG\Response(
     *     response=204,
     *     description="Returns empty body if deleted"
     * )
     * @SWG\Parameter(
     *     name="userId",
     *     in="path",
     *     type="integer",
     *     description="Id of the User to be deleted"
     * )
     * @Security(name="Bearer")
     */
    public function deleteUser(int $userId)
    {

        $userRepository = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepository->find($userId);

        if(!$user){
            $message = 'User not found!';

            return $this->handleView($this->view(compact('message'), 404));
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();

        return $this->handleView($this->view(null, 204));
    }

    /**
     * Get a user
     * @Rest\Get("/users/{userId}", requirements={"userId"="\d+"})
     * @param int $userId
     * @return Response
     * @SWG\Response(
     *     response=200,
     *     description="Returns User object"
     * )
     * @SWG\Parameter(
     *     name="userId",
     *     in="path",
     *     type="integer",
     *     description="Id of the User to be fetched"
     * )
     * @Security(name="Bearer")
     */
    public function getOneUser(int $userId){

        $userRepository = $this->getDoctrine()->getRepository(User::class);

        $user = $userRepository->find($userId);

        return $this->handleView($this->view($user));
    }

}
