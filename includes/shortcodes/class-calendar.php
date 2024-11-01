<?php

/**
 * @package WP_Attend
 */


class Calendar{

	/**
     * Constructor
     */
    public function __construct(){
        $this->naviHref = strtok($_SERVER['REQUEST_URI'], '?');
    }


    /********************* PROPERTY ********************/
    private $dayLabels = array("Mon","Tue","Wed","Thu","Fri","Sat","Sun");

    private $currentYear=0;

    private $currentMonth=0;

    private $currentDay=0;

    private $currentDate=null;

    private $daysInMonth=0;

    private $naviHref=null;

	public function show() {
		global $wpdb;
		$year = null;
        $month = null;

        if(null==$year&&isset($_GET['year'])){

            $year = sanitize_text_field($_GET['year']);

        }else if(null==$year){

            $year = date("Y",time());

        }

        if(null==$month&&isset($_GET['month'])){

            $month = sanitize_text_field($_GET['month']);

        }else if(null==$month){

            $month = date("m",time());

        }

        $this->currentYear=$year;

        $this->currentMonth=$month;

        $this->daysInMonth=$this->_daysInMonth($month,$year);

        $content = '';
		if(isset($_SERVER['QUERY_STRING'])) {
			$queries = array();
			parse_str( $_SERVER['QUERY_STRING'], $queries );
			if ( array_key_exists( 'result', $queries ) ) {
				$content = '<div id="ok-result-msg" style="display: block">' . esc_html($queries['result']) . '</div>';
			} else {
				$content = '<div id="ok-result-msg"></div>';
			}
		}
		else{
			$content = '<div id="ok-result-msg"></div>';
		}

        $content.= '<div id="error-result-msg"></div>'.
                  '<div id="calendar" >'.
                        '<div class="box">'.
                        $this->_createNavi().
                        '</div>'.
                        '<div class="box-content">'.
                                '<ul class="label">'.$this->_createLabels().'</ul>';
                                $content.='<div class="clear"></div>';
                                $content.='<ul class="dates">';

                                $weeksInMonth = $this->_weeksInMonth($month,$year);
                                // Create weeks in a month
                                for( $i=0; $i<$weeksInMonth; $i++ ){

                                    //Create days in a week
                                    for($j=1;$j<=7;$j++){
                                        $content.=$this->_showDay($i*7+$j);
                                    }
                                }

                                $content.='</ul>';

                                $content.='<div class="clear"></div>';

                        $content.='</div>';


		$content.='<div class="form-popup" id="addActivityForm">'.
					'<form class="form-container" id="aForm" >'.
						'<h3>New activity</h3>'.

						'<label for="description"><b>'.esc_html__('Description', 'wp-attend').'</b></label>'.
						'<input type="text" placeholder="Description" name="description" required>'.

						'<label for="location"><b>'.esc_html__('Location', 'wp-attend').'</b></label>'.
						'<input type="text" placeholder="Location" name="location">'.

						'<label for="time"><b>'.esc_html__('Time', 'wp-attend').'</b></label>'.
						'<input type="time" class="form-control" value="12:00" name="time" required>'.

		                '<div class="form-attendance">'.
							'<label for"emailSubscribers"><b>'.esc_html__('Send attendance email to subscribers', 'wp-attend').'</b></label>'.
							'<input type="checkbox" name="emailSubscribers">'.
						'</div>'.

						'<button type="submit" class="btn" name="btn_submit" onclick="createActivity(event)">'.esc_html__('Save', 'wp-attend').'</button>'.
						'<button type="button" class="btn cancel" onclick="closeForm()">'.esc_html__('Cancel', 'wp-attend').'</button>'.

						'<input type="hidden" name="date" id="date">'.
					'</form>'.
				'</div>';

		$content.='<div class="form-popup" id="editActivityForm">'.
			          '<form class="form-container" id="editForm" >'.
				          '<h3>Edit activity</h3>'.

				          '<label for="description"><b>'.esc_html__('Description', 'wp-attend').'</b></label>'.
				          '<input type="text" placeholder="Description" name="description" required>'.

				          '<label for="location"><b>'.esc_html__('Location', 'wp-attend').'</b></label>'.
				          '<input type="text" placeholder="Location" name="location" required>'.

				          '<label for="time"><b>'.esc_html__('Time', 'wp-attend').'</b></label>'.
				          '<input type="time" class="form-control" value="12:00" name="time" required>'.

				          '<div class="form-attendance">'.
					          '<label for"emailSubscribers"><b>'.esc_html__('Send updated version to subscribers', 'wp-attend').'</b></label>'.
					          '<input type="checkbox" name="emailSubscribers">'.
				          '</div>'.

				          '<button type="submit" class="btn" name="btn_submit" onclick="editActivity(event)">'.esc_html__('Save', 'wp-attend').'</button>'.
				          '<button type="button" class="btn cancel" onclick="closeEditForm()">'.esc_html__('Cancel', 'wp-attend').'</button>'.
		                  '<input type="hidden" name="id" id="id">'.
				          '<input type="hidden" name="date" id="date">'.
			          '</form>'.
		          '</div>';

        $content.='</div>';



        return $content;
    }

    /********************* PRIVATE **********************/
    /**
    * create the li element for ul
    */
    private function _showDay($cellNumber){

		$activities = null;
        if($this->currentDay==0){

            $firstDayOfTheWeek = date('N',strtotime($this->currentYear.'-'.$this->currentMonth.'-01'));

            if(intval($cellNumber) == intval($firstDayOfTheWeek)){

                $this->currentDay=1;

            }
        }

        if( ($this->currentDay!=0)&&($this->currentDay<=$this->daysInMonth) ){

            $this->currentDate = date('Y-m-d',strtotime($this->currentYear.'-'.$this->currentMonth.'-'.$this->currentDay));

            $cellContent = $this->currentDay;

            $this->currentDay++;

        }else{

            $this->currentDate =null;

            $cellContent=null;
        }
		if($this->currentDate!=null)
		{
			global $wpdb;
			$datefrom = $this->currentDate." 00:00:00";
			$dateto = $this->currentDate." 23:59:59";
			$result = $wpdb->get_results("Select * from wp_at_activities where timestamp between '$datefrom' and '$dateto' order by timestamp, id asc");
			if($result != null){
				$activities .= '<ul class="activities">';
				foreach($result as $activity){
					$time = strtotime($activity->timestamp);
					$activities .= '<li class="activity" id="attendance'.esc_attr($activity->id).'">'.esc_html(date('H:i', $time)." - ".$activity->description.
					' - '.$activity->location).
	                '<div class="attendance-button" onclick="displayAttendance('.esc_js($activity->id).', \''.esc_js($this->currentDate).'\')"></div>'.
	                '<div class="attendance-div" >'.
		                '<div class="activity-overview">'.
			                '<div class="activity-description"><b>'.esc_html__('Description', 'wp-attend').': </b>'.esc_html($activity->description).'</div>'.
			                '<div class="activity-location"><b>'.esc_html__('Location', 'wp-attend').': </b>'.esc_html($activity->location).'</div>'.
			                '<div class="activity-time"><b>'.esc_html__('Time', 'wp-attend').': </b>'.esc_html(date('H:i', $time)).'</div>'.
		                '</div>'.
						'<ul class="attend-list" id="willAttend'.esc_attr($activity->id).'" ondrop="handleDrop(this, event)" ondragover="handleDragOver(event)"><li class="title"></li></ul>'.
		                '<ul class="notattend-list" id="willNotAttend'.esc_attr($activity->id).'" ondrop="handleDrop(this, event)" ondragover="handleDragOver(event)"><li class="title"></li></ul>'.
		                '<ul class="yettorespond-list" id="yetToRespond'.esc_attr($activity->id).'" ondrop="handleDrop(this, event)" ondragover="handleDragOver(event)"><li class="title"></li></ul>'.
					'</div>'.
	                '<div class="remove" '.(is_user_logged_in()?'onclick="removeActivity(\''.esc_js($activity->id).'\')"':'disabled title="'.esc_attr__('You have to be logged in to delete activities', 'wp-attend').'"').'> x </div>'.
	                '<div class="edit" '.(is_user_logged_in()?'onclick="displayEditActivityForm(\''.esc_js($activity->id).'\')"':'disabled title="'.esc_attr__('You have to be logged in to edit activities', 'wp-attend').'"').'> </div></li>';
				}
				$activities .= '</ul>';
			}
		}


        return '<li id="li-'.esc_attr($this->currentDate).'" class="'.esc_attr($cellNumber%7==1?' start ':($cellNumber%7==0?' end ':' ')).
                ($cellContent==null?'mask':'').'">'.
				($cellContent==null?'':'<div class="btn-day" onclick="displayDayOverview(\''.esc_js($this->currentDate).'\')">'.
				'<div class="btn-div">'.
					'<p class="daytext">'.esc_html($cellContent).'</p>'.
					$activities.
				'</div></div>')
				.'</li>';
    }

    /**
    * create navigation
    */
    private function _createNavi(){

        $nextMonth = $this->currentMonth==12?1:intval($this->currentMonth)+1;

        $nextYear = $this->currentMonth==12?intval($this->currentYear)+1:$this->currentYear;

        $preMonth = $this->currentMonth==1?12:intval($this->currentMonth)-1;

        $preYear = $this->currentMonth==1?intval($this->currentYear)-1:$this->currentYear;

        return
            '<div class="header">'.
				        '<button class="return" id="return" onclick="'.esc_js('returnFromDayOverview(this.value)').'"><</button>'.
                '<a class="prev" id="prev" href="'.esc_url($this->naviHref.'?month='.sprintf('%02d',$preMonth)).'&year='.$preYear.'">Prev</a>'.
                '<span class="title">'.esc_html(date('Y M',strtotime($this->currentYear.'-'.$this->currentMonth.'-1'))).'</span>'.
				        '<button class="add" id="add" onclick="'.esc_js('displayActivityForm(this.value)').'" '.(is_user_logged_in()?'':'disabled title="'.esc_attr__('You have to be logged in to add activities', 'wp-attend').'"').'>+</button>'.
                '<a class="next" id="next" href="'.esc_url($this->naviHref.'?month='.sprintf("%02d", $nextMonth).'&year='.$nextYear).'">Next</a>'.
            '</div>';
    }

    /**
    * create calendar week labels
    */
    private function _createLabels(){

        $content ='<li class="start title title">'.esc_html__('Mon', 'wp-attend').'</li>';
	    $content .='<li class="start title title">'.esc_html__('Tue', 'wp-attend').'</li>';
	    $content .='<li class="start title title">'.esc_html__('Wed', 'wp-attend').'</li>';
	    $content .='<li class="start title title">'.esc_html__('Thu', 'wp-attend').'</li>';
	    $content .='<li class="start title title">'.esc_html__('Fri', 'wp-attend').'</li>';
	    $content .='<li class="start title title">'.esc_html__('Sat', 'wp-attend').'</li>';
	    $content .='<li class="start title title">'.esc_html__('Sun', 'wp-attend').'</li>';
        return $content;
    }



    /**
    * calculate number of weeks in a particular month
    */
    private function _weeksInMonth($month=null,$year=null){

        if( null==($year) ) {
            $year =  date("Y",time());
        }

        if(null==($month)) {
            $month = date("m",time());
        }

        // find number of days in this month
        $daysInMonths = $this->_daysInMonth($month,$year);

        $numOfweeks = ($daysInMonths%7==0?0:1) + intval($daysInMonths/7);

        $monthEndingDay= date('N',strtotime($year.'-'.$month.'-'.$daysInMonths));

        $monthStartDay = date('N',strtotime($year.'-'.$month.'-01'));

        if($monthEndingDay<$monthStartDay){

            $numOfweeks++;

        }

        return $numOfweeks;
    }

    /**
    * calculate number of days in a particular month
    */
    private function _daysInMonth($month=null,$year=null){

        if(null==($year))
            $year =  date("Y",time());

        if(null==($month))
            $month = date("m",time());

        return date('t',strtotime($year.'-'.$month.'-01'));
    }
}
