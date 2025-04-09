<?php
require_once './hardware/HardwareInterface.php';
require_once './hardware/DefaultHardware.php';

class Elevator {
    private $currentFloor;
    private $minFloor;
    private $maxFloor;
    private $doorsOpen;
    private $hardware;

    public function __construct(HardwareInterface $hardware, $minFloor = -1, $maxFloor = 3, $startFloor = 0)
    {
        $this->minFloor = $minFloor;
        $this->maxFloor = $maxFloor;
        $this->currentFloor = $startFloor;
        $this->doorsOpen = false;
        $this->hardware = $hardware;

    }

    public function getCurrentFloor(){
        return $this->currentFloor;
    }

    public function areDoorsOpen(){
        return $this->doorsOpen;
    }

    public function openDoors(){
        $this->doorsOpen = true;
    }

    public function closeDoors(){
        $this->doorsOpen = false;
    }

    public function goUp(){
        if($this->currentFloor < $this->maxFloor){
            $this->closeDoors();
            $this->hardware->goUp();
            $this->currentFloor++;
            return $this->currentFloor;
        }
        return $this->currentFloor;
    }

    public function goDown(){
        if($this->currentFloor > $this->minFloor){
            $this->closeDoors();
            $this->hardware->goDown();
            $this->currentFloor--;
            return $this->currentFloor;
        }else{
            return $this->currentFloor;
        }
    }

    public function gotToFloor($floor){
        if($floor < $this->minFloor || $floor > $this->maxFloor){
            return "The request floor ($floor) is out of range. Please enter a valid floor.";
        }
        $this->closeDoors();
        if($floor > $this->currentFloor){
            while($this->currentFloor < $floor){
                $this->goUp();
            }
        }elseif($floor < $this->currentFloor){
            while($this->currentFloor > $floor){
                $this->goDown();
            }
        }
        $this->openDoors();
        return $this->currentFloor;
    }
}

session_start();


if(!isset($_SESSION['elevator'])){
    $hardware = new DefaultHardware();
    $_SESSION['elevator']= new Elevator($hardware);
}

$elevator = $_SESSION['elevator'];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    header('content-type: application/json');

    $floor = $_POST['floor'] ?? null;
    if($floor !== null){
        $currentFloor = $elevator->gotToFloor((int)$floor);
        echo json_encode([
            'currentFloor' => $currentFloor,
            'doorsOpen' => $elevator->areDoorsOpen()
        ]);
    }else{
        echo json_encode([
            'currentFloor' => $elevator->getCurrentFloor(),
            'doorsOpen' => $elevator->areDoorsOpen()
        ]);
    }
}

?>