import React, {Component} from 'react'
import MemberBirthExpBlock from "./MemberBirthExpBlock";
import {getDepartments} from '../api/Api'
import {getPositions} from '../api/Api'
import {withTranslation} from 'react-i18next'
import data from '../data/data.json';
 
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

    renderOptions = position => {
        const { t } = this.props;
        const renderDepartments = this.state.departments.map( (departament, index) => (
            <span key={index} onClick={() => {this.updateCurrentDepartment(departament, index); if (departament.id === this.state.selectedDepartment) {this.setState({selectedDepartment: -1}); this.resetFilter()} else {this.setState({selectedDepartment: departament.id})} }} data-val={departament.name} data-id={departament.id} className={departament.id === this.state.selectedDepartment ? 'filter-name m-active' : 'filter-name'}>{departament.name}</span>
        ));
        const renderPositions = this.state.positions.map( (position, index) => (
            <span key={index} onClick={() => {this.updateCurrentPosition(position, index); if (position.id === this.state.selectedPosition) {this.setState({selectedPosition: -1}); this.resetFilter()} else { this.setState({selectedPosition: position.id}) } }} data-val={position.name} className={position.id === this.state.selectedPosition ? 'filter-name m-active' : 'filter-name'}>{position.name}</span>
        ));
        return (
            <div className="filter-common">
                <div className="filter-inner">
                    <span className="filter-tittle">{t(data.departments)}</span>
                    {renderDepartments}
                </div>
                <div className="filter-inner">
                    <span className="filter-tittle">{t(data.positions)}</span>
                    {renderPositions}
                </div>
                <span className="js-reset-filter m-reset-button" onClick={this.resetFilter}>{t(data.clean)}</span>
            </div>
        );
    };

    render() {
        return (
            <div className="sidebar-inner">
                {this.renderOptions()}
                <br />
                <MemberBirthExpBlock />
            </div>
        );
    }
}

export default withTranslation()(Sidebar)
