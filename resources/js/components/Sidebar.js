import React, {Component} from 'react'
import MemberBirthExpBlock from "./MemberBirthExpBlock";
import {getDepartments} from '../api/Api'
import {getPositions} from '../api/Api'

class Sidebar extends Component {

    constructor (props) {
        super(props)
        this.state = {
            departments: [],
            positions: [],
            selectedItemId: null,
            selectedItemState: false
        }
        this.updateCurrentDepartment = this.updateCurrentDepartment.bind(this);
        this.updateCurrentPosition = this.updateCurrentPosition.bind(this);
    }

    state = {
        selectedDepartment: -1,
        selectedPosition: -1
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

    render() {
        return (
            <div className="sidebar-inner">
                {this.renderDepartment()}
                {this.renderPositions()}
                <span className="js-reset-filter m-reset-button" onClick={this.resetFilter}>Clean</span>
                <br />
                <MemberBirthExpBlock />
            </div>
        );
    }
}

export default Sidebar
