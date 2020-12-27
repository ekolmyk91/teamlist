import React, {Component} from 'react'
import { Link } from 'react-router-dom'
import {getDepartments} from "../api/Api";

class Header extends Component {

    render() {
        return (
            <header>
                <div className="wrapper">
                    <div className="divHeader">
                        <a title="Logo" href="index.html" className="header-logo">
                            <svg>
                                <use xlinkHref='#logo' />
                            </svg>
                        </a>
                        <ul className="navMenu">
                            <li>
                                <Link to='home'>Главная</Link>
                            </li>
                            <li>
                                <Link to='/'>Команда</Link>
                            </li>
                            <li>
                                <Link to='/logout'>Выйти</Link>
                            </li>
                        </ul>
                    </div>
                    <a className="hamburger js-navOpenMenu">
                        <span></span>
                    </a>
                </div>
            </header>
        );
    }
}
export default Header
