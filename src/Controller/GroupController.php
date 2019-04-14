<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\User;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Nelmio\ApiDocBundle\Annotation\Security;
use Swagger\Annotations as SWG;

/**
 * User controller
 * @Route("/api", name="api_")
 */
class GroupController extends AbstractFOSRestController
{

    /**
     * Get all groups
     * @Rest\Get("/groups")
     * @return Response
     * @SWG\Response(
     *     response=200,
     *     description="Returns the list of groups"
     * )
     * @Security(name="Bearer")
     */
    public function getGroups()
    {
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);

        $groups = $groupRepository->findAll();

        $view = $this->view(compact('groups'));
        $view->getContext()->enableMaxDepth();

        return $this->handleView($view);
    }

    /**
     * Create a new Group
     * @Rest\Post("/groups")
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return Response
     * @SWG\Response(
     *     response=201,
     *     description="Returns the created object"
     * )
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="list of parameters to create a group",
     *     required=true,
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(
     *            type="object",
     *            @SWG\Property(property="name", type="string"),
     *         )
     *     )
     * )
     * @Security(name="Bearer")
     */
    public function createGroup(Request $request, ValidatorInterface $validator)
    {
        $name = $request->request->get('name');

        $group = new Group();
        $group->setName($name);

        $errors = $validator->validate($group);
        if (count($errors) > 0) {
            return $this->handleView($this->view(compact('errors'), 400));
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($group);
        $em->flush();

        return $this->handleView($this->view($group, 201));
    }

    /**
     * Delete a Group
     * @Rest\Delete("/groups/{groupId}", requirements={"groupId"="\d+"})
     * @param int $groupId
     * @return Response
     * @SWG\Response(
     *     response=204,
     *     description="Returns empty body if deleted"
     * )
     * @SWG\Parameter(
     *     name="groupId",
     *     in="path",
     *     type="integer",
     *     description="Id of the Group to be deleted"
     * )
     * @Security(name="Bearer")
     */
    public function deleteGroup(int $groupId)
    {

        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $group = $groupRepository->find($groupId);

        if(!$group){
            $message = 'Group not found!';

            return $this->handleView($this->view(compact('message'), 404));
        }

        if($group->getUsers()->count() > 0){
            $message = 'Cannot delete Group, remove all assigned Users first!';

            return $this->handleView($this->view(compact('message'), 203));
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($group);
        $em->flush();

        return $this->handleView($this->view(null, 204));
    }

    /**
     * Get all the users from a Group
     * @Rest\Get("/groups/{groupId}/users", requirements={"groupId"="\d+"})
     * @param int $groupId
     * @return Response
     * @SWG\Response(
     *     response=201,
     *     description="Returns the created object"
     * )
     * @SWG\Parameter(
     *     name="groupId",
     *     in="path",
     *     type="integer",
     *     description="Id of the Group to have the users fetched"
     * )
     * @Security(name="Bearer")
     */
    public function getUsers($groupId)
    {
        /**
         * Check if the Group exists in the database
         * */
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $group = $groupRepository->find($groupId);
        if(!$group){
            $message = 'Group not found!';
            return $this->handleView($this->view(compact('message'), 404));
        }

        $users = $group->getUsers();

        $view = $this->view(compact('users'));
        $view->getContext()->enableMaxDepth();

        return $this->handleView($view);
    }

    /**
     * Assign an user to a Group
     * @Rest\Post("/groups/{groupId}/users", requirements={"groupId"="\d+"})
     * @param Request $request
     * @param int $groupId
     * @return Response
     * @SWG\Response(
     *     response=201,
     *     description="Returns the updated group"
     * )
     * @SWG\Parameter(
     *     name="groupId",
     *     in="path",
     *     type="integer",
     *     description="Id of the group to have a user assigneds"
     * )
     * @SWG\Parameter(
     *     name="body",
     *     in="body",
     *     description="list of parameters to assing a user to a group",
     *     required=true,
     *     @SWG\Schema(
     *         type="array",
     *         @SWG\Items(
     *            type="object",
     *            @SWG\Property(property="groupId", type="integer"),
     *         )
     *     )
     * )
     * @Security(name="Bearer")
     */
    public function assignUser(Request $request, $groupId)
    {

        $userId = $request->request->get('userId');
        if(!is_int($userId)){
            $message = 'User id is invalid!';
            return $this->handleView($this->view(compact('message'), 400));
        }

        /**
         * Check if the Group exists in the database
         * */
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $group = $groupRepository->find($groupId);
        if(!$group){
            $message = 'Group not found!';
            return $this->handleView($this->view(compact('message'), 404));
        }

        /**
         * Check if the User exists in the database
         * */
        $userRepository = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepository->find($userId);
        if(empty($user)){
            $message = "The User doesn't exists!";
            return $this->handleView($this->view(compact('message'), 404));
        }

        /**
         * Check if the User is assigned to the group
         * */
        if($group->getUsers()->contains($user)){
            $message = "The user {$user->getUsername()} already belongs to the group {$group->getName()}!";
            return $this->handleView($this->view(compact('message'), 203));
        }

        /**
         * If pass all the checks, then add the group to the user and persist the data
         */
        $user->addGroup($group);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $view = $this->view(compact('group'));
        $view->getContext()->enableMaxDepth();

        return $this->handleView($this->view($view, 201));
    }

    /**
     * Remove an user from a Group
     * @Rest\Delete("/groups/{groupId}/users/{userId}", requirements={"groupId"="\d+", "userId"="\d+"})
     * @param int $groupId
     * @param int $userId
     * @return Response
     * @SWG\Response(
     *     response=204,
     *     description="Returns empty body if the user was removed"
     * )
     * @SWG\Parameter(
     *     name="groupId",
     *     in="path",
     *     type="integer",
     *     description="Id of the group to have a user removed"
     * )
     * @SWG\Parameter(
     *     name="userId",
     *     in="path",
     *     type="integer",
     *     description="Id of the user to be removed to the group"
     * )
     * @Security(name="Bearer")
     */
    public function removeUser($groupId, $userId)
    {
        /**
         * Check if the Group exists in the database
         * */
        $groupRepository = $this->getDoctrine()->getRepository(Group::class);
        $group = $groupRepository->find($groupId);
        if(!$group){
            $message = 'Group not found!';
            return $this->handleView($this->view(compact('message'), 404));
        }

        /**
         * Check if the User exists in the database
         * */
        $userRepository = $this->getDoctrine()->getRepository(User::class);
        $user = $userRepository->find($userId);
        if(empty($user)){
            $message = "User not found!";
            return $this->handleView($this->view(compact('message'), 404));
        }

        /**
         * Check if the User is assigned to the group
         * */
        if(!$group->getUsers()->contains($user)){
            $message = "The user {$user->getUsername()} doesn't belong to the group {$group->getName()}!";
            return $this->handleView($this->view(compact('message'), 404));
        }

        /**
         * If pass all the checks, then remove the user from the group and persist the data
         */
        $user->removeGroup($group);

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return $this->handleView($this->view(null, 204));
    }

}
