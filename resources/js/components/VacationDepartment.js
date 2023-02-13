import React, {Component} from 'react';
import {withTranslation} from 'react-i18next';
import VacationInfoPopup from './VacationInfoPopup'

class VacationDepartment extends Component {
    constructor (props) {
        super(props)
        this.state = {
            list: false,
            showPopupId: false,
        }
    }

    toggleList = () => {
        this.setState({
            list: !this.state.list
        });
    }

    togglePopup(id, e) {
        this.setState({
            showPopupId: id ? id : null,
            stateClass: 'overlay--show'
        });
        $(".overlay").toggleClass("overlay--show")
        $('body').toggleClass("m-ovrf")
    }

    render() {

        const { t } = this.props;
        const titleStyle = {
            background: this.props.department.background
        }
        const listStyle = {
            boxShadow: `inset 0px 0px 15px 0px ${this.props.department.background}`
        }
        return (
            <div className='departmen'>
                <div className='departmen__title' style={titleStyle} onClick={this.toggleList}>{this.props.department.name}</div>
                {
                    this.state.list &&
                    <ul className='departmen__list' style={listStyle}>
                        {
                            this.props.employees.map(employee => {
                                return (
                                    <li className='departmen__item' key={employee.user_id} >
                                        <div onClick={this.togglePopup.bind(this, employee.user_id)}>{employee.surname} {employee.name}</div>
                                        {this.state.showPopupId == employee.user_id ?
                                            <VacationInfoPopup member={employee} stateClass={this.state.stateClass} closePopup={this.togglePopup.bind(this)} /> : null
                                        }
                                    </li>

                                )
                            })
                        }
                    </ul>
                }
            </div>
        );
    }
}

export default withTranslation()(VacationDepartment)
