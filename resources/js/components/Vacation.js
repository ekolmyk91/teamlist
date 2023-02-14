import React, {Component} from 'react';
import {getUsers, getCalendar, getVacation, getDepartments, getCurrentUser} from '../api/Api';
import {withTranslation} from 'react-i18next';
import data from '../data/data.json';
import { Calendar, momentLocalizer } from 'react-big-calendar';
import moment from 'moment';
import VacationDepartment from './VacationDepartment'
import VacationStatistic from './VacationStatistic';
import VacationInfoPopup from './VacationInfoPopup'

const localizer = momentLocalizer(moment)

class Vacation extends Component {
    constructor (props) {
        super(props)
        this.state = {
            members: [],
            calendar: [],
            eventsList: [],
            year: new Date().getFullYear(),
            month: new Date().getMonth() + 1,
            departments:[],
            statistic: [],
            showPopupId: false,
            popup: null
        }
    }

    componentDidMount () {

        getUsers().then(data => {
            this.setState({
                members: data
            });
        });

        getCurrentUser().then(data => {
			this.setState({
				statistic: data,
			});
		})

        getCalendar(this.state.year, this.state.month).then(data => {
            this.setState({
                calendar: data
            });
        });

        getVacation(this.state.year, this.state.month).then(data => {
            this.setState({
                eventsList: data
            });
        });

        getDepartments().then(data => {
            this.setState({
                departments: data
            });
        });
    }

    numberСheck(num) {
        if(String(num).length === 1) {
            let updateNum = '0' + String(num)
            return updateNum
        } else {
            return num
        }
    }

    updateDate(date) {
        let curr_date = date.getDate();
        let curr_month = date.getMonth() + 1;
        let curr_year = date.getFullYear();

        return curr_year + "-" + this.numberСheck(curr_month) + "-" + this.numberСheck(curr_date)
    }

    changeNavigate = (time) => {
        let year = time.getFullYear();
        let month = time.getMonth() + 1;
        getVacation(year, month).then(data => {
            this.setState({
                eventsList: data
            });
        });
        getCalendar(year, month).then(data => {
            this.setState({
                calendar: data
            });
        });
    }

    dayStyle = (date) => {
        let allDate = this.updateDate(date)
        let day = this.state.calendar.filter(day => day.dt === allDate)
        if(
            day.length && day[0].day_of_week === 7  && day[0].is_weekday === 0 ||
            day.length && day[0].day_of_week === 1 && day[0].is_weekday === 0  ||
            day.length && day[0].day_of_week != 1 && day[0].day_of_week != 7 && day[0].is_weekday === 0 && day[0].is_holiday === 1
        ) {
                return {
                    style: {
                    backgroundColor: '#FFC4C7',
                    }
                }
        }
    }

    eventStyleGetter = (event) => {
        let user = this.state.members.filter(member => member.user_id === event.user_id)
        let color = this.state.departments.filter(dep => dep.id === user[0].department.id)
        if(color.length) {
            return {
                style: {
                    backgroundColor: color[0].background,
                    fontSize: '12px',
                    fontWeight: '600'
                }
            }
        }
    }

    togglePopup(id, e) {
        this.setState({
            showPopupId: id ? id : null,
            stateClass: 'overlay--show',
            popup: null
        });
        $(".overlay").toggleClass("overlay--show")
        $('body').toggleClass("m-ovrf")
    }

    handleSelectEvent = (event) => {
        if(event) {
            this.setState({
                showPopupId: event.user_id ? event.user_id : null,
                stateClass: 'overlay--show',
                popup: event.user_id ? <VacationInfoPopup userId={event.user_id} stateClass={this.state.stateClass} closePopup={this.togglePopup.bind(this)}  vacation={event}/> : null
            });
            $(".overlay").toggleClass("overlay--show")
            $('body').toggleClass("m-ovrf")
        }
    }

    render() {
        const { t } = this.props;
        return (
            <div>
                <section className="pageHeaderForm">
                    <div className="wrapper">
                        <h2>{t(data.vacation)}</h2>
                    </div>
                </section>
                <div className="container vacation">
                    <VacationStatistic statistic={this.state.statistic.statistic}/>
                    <div className='vacation__departmens'>
                        {
                            this.state.departments.map(dep => {
                                let employees = this.state.members.filter(member => {
                                    return member.department.id === dep.id
                                })
                                return <VacationDepartment employees={employees} department={dep} key={dep.id}/>
                            })
                        }
                    </div>
                    <Calendar
                        localizer={localizer}
                        events={this.state.eventsList}
                        startAccessor="start"
                        endAccessor="end"
                        defaultView="month"
                        selectable={true}
                        popup={true}
                        style={{ height: 700 }}
                        onNavigate={(data) => this.changeNavigate(data)}
                        dayPropGetter={this.dayStyle}
                        eventPropGetter={(this.eventStyleGetter)}
                        showAllEvents={true}
                        onSelectEvent={this.handleSelectEvent}
                    />
                    {this.state.popup}
                </div>
            </div>
        );
    }
}

export default withTranslation()(Vacation)
