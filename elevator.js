document.addEventListener("DOMContentLoaded", function(){
    const elevator = document.getElementById('elevator');
    const doors = document.getElementById('doors');
    const floorDisplay = document.getElementById('floorDisplay');
    const buttons = document.querySelectorAll('button[data-floor]');

    const floorHeight = 80;
    const floors = [-1, 0, 1, 2, 3];
    let currentFloor = 0;
    let moving = false;

    function setInitialPosition(){
        const index = floors.indexOf(currentFloor);
        const position = index * floorHeight;
        elevator.style.bottom = `${position}px`;
        doors.classList.add('closed');
    }

    window.addEventListener('load',setInitialPosition);

    buttons.forEach(button => {
        button.addEventListener('click', () => {
            const targetFloor = parseInt(button.getAttribute('data-floor'));
            if(!moving && targetFloor !== currentFloor){
                prepareMove(targetFloor);
            }
        })
    })

    function prepareMove(targetFloor){
        moving = true;
        doors.classList.remove('closed');
        doors.classList.add('open');
        doors.addEventListener('transitionend', () => {
            moveElevator(targetFloor);
        }, {once: true})
    }

    function moveElevator(targetFloor){
        fetch('elevator.php',{
            method: 'POST',
            headers: {
                'Content-Type' : 'application/x-www-form-urlencoded',
            },
            body: `floor=${targetFloor}`,
        })
        .then(res => {
            if(!res.ok){
                throw new Error(`HTTP error! status: ${res.status}`);
                
            }
            return res.json();
        })
        .then(data => {
            if(data.currentFloor !== undefined){
                currentFloor = data.currentFloor;
                floorDisplay.textContent = currentFloor;
                updateElevatorPosition();
                if(data.doorsOpen){
                    setTimeout(() => {
                        doors.classList.remove('open');
                        doors.classList.add('closed');
                    },1000)
                }
                moving = false;
            }
        })
        .catch(err => {
            console.error('Error', err);
        })
    }

    function updateElevatorPosition(){
        const index = floors.indexOf(currentFloor);
        const position = index * floorHeight;
        elevator.style.bottom = `${position}px`;
    }
})