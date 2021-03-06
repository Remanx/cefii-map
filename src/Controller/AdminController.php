<?php
// src/Controller/Admincontroller.php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\StudentLocation;
use App\Repository\StudentLocationRepository;
use App\Form\LocationType;


class AdminController extends Controller
{

    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        $em = $this->getDoctrine()->getManager();
        $em->getRepository(StudentLocation::class)->createXMLMarkers();

        return $this->render('map/map.html.twig');
    }

    /**
     * @Route("/admin", name="admin")
     */
    public function adminIndex(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $studentLocations = $em->getRepository(StudentLocation::class)->findAll();

        $locations = new StudentLocation();
        $form = $this->createForm(LocationType::class, $locations);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $postcode = $locations->getPostCode();
            $city = $locations->getCity();
            $country = $locations->getCountry();

            $address = $postcode." ".$city." ".$country;

            $location = $em->getRepository(StudentLocation::class)->geocoder($address);
            $locations->setLatitude($location[0]);
            $locations->setLongitude($location[1]);

            $em->persist($locations);
            $em->flush();

            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/admin.html.twig', [
            'form' => $form->createView(),
            'locations' => $studentLocations
        ]);
    }

    /**
     * @Route("/admin/update/{id}", name="updateLocation")
     */
    public function updateLocation(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $locations = $em->find(StudentLocation::class, $id);
        $studentLocations = $em->getRepository(StudentLocation::class)->findAll();

        $form = $this->createForm(LocationType::class, $locations);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $postcode = $locations->getPostCode();
            $city = $locations->getCity();
            $country = $locations->getCountry();

            $address = $postcode." ".$city." ".$country;

            $location = $em->getRepository(StudentLocation::class)->geocoder($address);
            $locations->setLatitude($location[0]);
            $locations->setLongitude($location[1]);

            $em->flush();

            return $this->redirectToRoute('admin');
        }

        return $this->render('admin/admin.html.twig', [
            'form' => $form->createView(),
            'locations' => $studentLocations
        ]);
    }

    /**
     * @Route("/admin/delete/{id}", name="deleteLocation")
     */
    public function deleteLocation(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $location = $em->find(StudentLocation::class, $id);

        $em->remove($location);
        $em->flush();

        return $this->redirectToRoute('admin');
    }

}
