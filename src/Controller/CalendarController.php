<?php
// src/Controller/CalendarController.php
namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\Month;
use App\Entity\Patient;
use App\Form\Type\PatientType;
use DateInterval;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class CalendarController extends AbstractController
{ 
    
    protected $weekDays = ['понеделник', 'вторник', 'сряда', 'четвъртък', 'петък', 'събота', 'неделя'];
    protected $monthNames = ['Януари', 'Февруари', 'Март', 'Април', 'Май', 'Юни', 'Юли', 'Август', 'Септември', 'Октомври', 'Ноември', 'Декември'];

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $locale = "bg_BG";
        // some logic to determine the $locale
        $request->setLocale($locale);
    }

    /**
     * @Route("/", name="app_calendar_index")
    */
    public function index(Request $request, TranslatorInterface $translator)
    {
        // Взимаме данните от конфигурационния файл /config/$app_config.php

        $adminEmail = $this->getParameter('app.admin_email');
        $workingHours = $this->getParameter('app.working_hours');
        $workingDays = $this->getParameter('app.working_days');
        $monthsInAdvance = $this->getParameter('app.months_in_advance');

        // Инициализираме необходимите обекти за работа с базата данни и енкодването на обект в JSON
        $entityManager = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository(Appointment::class);
        
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);

        // Инициализираме необходимите променливи за работа с календара - текущи, предишни, следващи, максимални месец и година
        $error = ['date' => null, 'hour' => null];
        $year = date('Y');
        $maxYear = $year;
        $rMonth = date('m');
        $maxMonth = $rMonth + $monthsInAdvance;
        if ($maxMonth > 12) 
        {
            $maxMonth -= 12;
            $maxYear = $year + 1;
        }
        if ($request->isMethod('get'))
        {
            if ($request->query->get('year')) 
            {
                $year = $request->query->get('year');
            }
            if ($request->query->get('month')) {
                $rMonth = $request->query->get('month');
            }
        }
        
        if ($year > $maxYear)
        {
            $year = $year - 1;
            $rMonth = 1;
        }
        if ($rMonth > $maxMonth && $year == $maxYear)
        {
            $rMonth = $rMonth - 1;
        }
        // Създаваме обект от тип Month, който връща всички дни от седмиците, които включва, както и информация за самия месец(първи ден от първа седмица, първи ден от месеца и т.н.)
        $month = Month::createFromDate($year.'-'.$rMonth.'-1');
        $days = $month->getDays();
        $monthInfo = $month->getInfo();
        // Инициализираме няколко променливи, както и взимаме всички записи от БД за избрания месец(използваме една заявка към базата, защото информацията от нея е сравнително малка по обем и е по-лесно да бъде обработена извън нея, вместо да се правят множество заявки към БД)
        $calendar = [];
        $monthHeader = $this->weekDays;
        $counter = 0;
        $weeks = ceil(count($days) / 7);
        $appointments = $repository->getAppointmentsForMonth($monthInfo['firstDay'], $monthInfo['lastDay']);
        $workingHoursA = explode('-', $workingHours);
        
        $patient = new Patient();
        $form = $this->createForm(PatientType::class, $patient);

        // Проверяваме дали имаме POST заявка, т.е. имаме изпратена форма
        if ($request->isMethod('post'))
        {
            $form->handleRequest($request);

            // Ако формата е изпратена и валидна
            if ($form->isSubmitted() && $form->isValid()) {
                
                $patient_data = $form->getData();

                // Проверяваме дали имаме записана заявка за същата дата/час
                $appointmentCheck = $repository->findBy(['for_date' => $request->request->get('date'), 'for_hour' => $request->request->get('hour')]);
                if (empty($appointmentCheck))
                {
                    $appointment = new Appointment();
                    // Попълваме необходимите данни за записа в базата данни
                    $appointment->setForDate($request->request->get('date'));
                    $appointment->setForHour($request->request->get('hour'));
                    // Обръщаме данните за потребителя/пациента в JSON
                    $arr = $serializer->normalize($patient_data, 'null', [AbstractNormalizer::ATTRIBUTES => ['name', 'email', 'phone']]);
                    $appointment->setPatientInfo($serializer->serialize($arr, 'json'));
                    $appointment->setCreatedAt(date('Y-m-d H:i:s'));
                    $entityManager->persist($appointment);
                    // Записваме
                    $entityManager->flush();
                }
                // Пренасочваме потребителя, основната цел е да изчистим данните за формата от браузъра
                return $this->redirectToRoute('app_calendar_index', array('year' => date('Y', strtotime($request->request->get('date'))), 'month' => date('m', strtotime($request->request->get('date')))));
            } else if (!$form->isValid())
            {
                // Ако формата е невалидна то попълваме масива $error, който ни трябва да покажем формата, която е модален диалог
                $error['date'] = date('d.m.Y', strtotime($request->request->get('date')));
                $error['hour'] = $request->request->get('hour');
            }
            
        }
        
        // Инициализация на седмицата от месеца
        $calendarWeek = [];
        // Завъртаме цикъл за обхождане на всички дни от седмиците на месеца(включително дни на предишен месец в първата седмица и дни на следващ месец в последната седмица, ако има такива)
        while($counter < count($days))
        {
            // Изчисляваме начален и краен час за деня спрямо работното време(използваме DateTime обект, за по-точна итерация, 
            // особено при евентуално избиране на по-нестандартен период от 1 час)
            $startHour = new DateTime('2020-01-01 '.$workingHoursA[0].':00');
            $endHour = new DateTime('2020-01-01 '.$workingHoursA[1].':00');
            // Създаваме масива $day, който съдържа необходимата за показване и за логика информация за всеки ден
            $day = [];
            $day['dateShow'] = date('d.m.Y', strtotime($days[$counter]));
            $day['date'] = $days[$counter];
            $day['dayNumber'] = intval(date('d', strtotime($days[$counter])));
            $day['dayOfWeek'] = date('w', strtotime($days[$counter]));
            $day['month'] = date('m', strtotime($days[$counter]));
            $hours = [];
            $h=$startHour;
            $baseHours = [];
            // Завъртаме цикъл за работните дни за всеки работен ден
            while($h<=$endHour)
            {
                $baseHours[] = $hour = $h->format('H:i');
                $hours[$hour] = null;
                // Обхождаме масива $appointments, с всички заявки за месеца, като филтрираме тези за текущия ден/час и ги прикрепваме към масива $hours
                if ($day['date'] >= $monthInfo['firstDay'] && $day['date'] <= $monthInfo['lastDay'])
                {
                    
                    if (array_key_exists($day['date'], $appointments))
                    {
                        if (array_key_exists($hour, $appointments[$day['date']]))
                        {
                            
                            if (!array_search($hour, $appointments[$day['date']]))
                            {
                                $hours[$hour] = $appointments[$day['date']][$hour];
                            }
                        }
                    }
                    
                }
                
                // Добавяме $hours добавяме към $day
                $day['hours'] = $hours;
                $h->add(new DateInterval('PT1H'));
                
            }
            
            
            $calendarWeek[] = $day;
            $counter++;
            if (($counter % 7) == 0)
            {
                // При всяка нова седмица добавяме седмичния масив към месеца и го реинициализираме
                $calendar[] = $calendarWeek;
                $calendarWeek = [];
            } else
            {
                
            }
        }
        // dd($calendar);
        
          
        
        
        // Информация за предишен, текущ(избран от календара), текущ(според системния календар) и следващ месеци, необходими за логиката в темплейт файла
        $mData = [];
        // $rMonth = 1;
        $mData['current']['year'] = $year;
        $mData['current']['fYear'] = date('Y');
        $mData['current']['name'] = $this->monthNames[$rMonth - 1];
        $mData['current']['number'] = $rMonth;
        $mData['current']['month'] = date('m');
        $mData['current']['today'] = date('Y-m-d');
        $mData['max']['number'] = $maxMonth;
        $mData['max']['year'] = $maxYear;
        $previousMonth = $rMonth - 1;
        $nextMonth = $rMonth + 1;
        $mData['previous']['year'] = $year;
        $mData['next']['year'] = $year;
        if ($previousMonth < 1) 
        {
            $previousMonth = 12;
            $mData['previous']['year'] = $year - 1;
        }
        if ($nextMonth > 12) 
        {
            $nextMonth = 1;
            $mData['next']['year'] = $year + 1;
        }

        $mData['previous']['name'] = $this->monthNames[$previousMonth - 1];
        $mData['previous']['number'] = $previousMonth;

        $mData['next']['name'] = $this->monthNames[$nextMonth - 1];
        $mData['next']['number'] = $nextMonth;

        // Викаме темплейт файла, като му подаваме необходимите данни
                
        return $this->render('base.html.twig', ['workingDays' => $workingDays, 'monthHeader' => $monthHeader, 'calendar' => $calendar, 'baseHours' => $baseHours, 'form' => $form->createView(), 'month' => $mData, 'error' => $error]);
    }

    
}