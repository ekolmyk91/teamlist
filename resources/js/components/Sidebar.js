import React, {Component} from 'react'
import moment from 'moment'
import {getBirthPeople, getDepartments} from '../api/Api'
import {getPositions} from '../api/Api'

class Sidebar extends Component {

    constructor (props) {
        super(props)
        this.state = {
            departments: [],
            positions: [],
            selectedItemId: null,
            selectedItemState: false,
            monthValue: moment().month(),
            birthPeople: []
        }
        this.updateCurrentDepartment = this.updateCurrentDepartment.bind(this);
        this.updateCurrentPosition = this.updateCurrentPosition.bind(this);
        this.handleMonthChange = this.handleMonthChange.bind(this);
    }

    state = {
        selectedDepartment: -1,
        selectedPosition: -1,
    }

    componentDidMount () {
        getDepartments().then(data => {
            this.setState({
                departments: data,
            });
        })
        getPositions().then(data => {
            this.setState({
                positions: data,
            });
        })
        getBirthPeople(this.state.monthValue+1).then(data => {
            this.setState({
               birthPeople: data,
            });
        })
    }

    updateCurrentDepartment = (e, departament, name) => {
        name = e.name;
        this.props.updateDepartment(departament, name)
    };

    updateCurrentPosition = (e, position, name) => {
        name = e.name;
        this.props.updatePosition(position, name)
    };

    resetFilter = (departament, position) => {
        this.setState({
            selectedDepartment: -1,
            selectedPosition: -1
        });
        this.props.updateDepartment(departament, undefined)
        this.props.updatePosition(position, undefined)
    };

    handleMonthChange(event) {
        this.setState({monthValue: event.target.value});
        event.preventDefault();
    }

    renderDepartment () {
        const renderDepartments = this.state.departments.map( (departament, index) => (
            <span key={index} onClick={() => {this.updateCurrentDepartment(departament, index), this.setState({selectedDepartment: departament.id})}} data-val={departament.name} data-id={departament.id} className={departament.id === this.state.selectedDepartment ? 'filter-name m-active' : 'filter-name'}>{departament.name}</span>
        ));
        return (
            <div className="filter-inner">
                <span className="filter-tittle">Departments</span>
                {renderDepartments}
            </div>
        );
    };

    renderPositions = position => {
        const renderPositions = this.state.positions.map( (position, index) => (
            <span key={index} onClick={() => {this.updateCurrentPosition(position, index), this.setState({selectedPosition: position.id})}} data-val={position.name} className={position.id === this.state.selectedPosition ? 'filter-name m-active' : 'filter-name'}>{position.name}</span>
        ));
        return (
            <div className="filter-inner">
                <span className="filter-tittle">Positions</span>
                {renderPositions}
            </div>
        );
    };

    getPositions

    renderMonthsList = () => {
        const monthList = moment.months();
        return (
            <div>
                <span>Birthday People</span>
                <select value={this.state.monthValue} onChange={this.handleMonthChange}>
                    {monthList.map((month, index) => (
                        <option key={index} value={index}>{month}</option>
                    ))}
                </select>
                {/*{this.state.monthValue}*/}
            </div>
            );
    }

    renderBirthPeople = () => {
        const renderBirthPeople = this.state.birthPeople.map( (member, id) => (
            <tr key={id}>
                <td>{member.name}</td>
                <td>{member.surname}</td>
                <td>{(new Date(member.birthday).toLocaleDateString('en-GB', {
                    month: '2-digit',day: '2-digit'}))}</td>
            </tr>
        ));
        return (
            <div>
                <table>
                    <tbody>
                        {renderBirthPeople}
                    </tbody>
                </table>
            </div>
        );
    }

    render() {
        return (
            <div className="sidebar-inner">
                {this.renderDepartment()}
                {this.renderPositions()}
                <span className="js-reset-filter m-reset-button" onClick={this.resetFilter}>Clean</span>
                <br />
                {this.renderMonthsList()}
                <br />
                {this.renderBirthPeople()}
            </div>
        );
    }
}

export default Sidebar
