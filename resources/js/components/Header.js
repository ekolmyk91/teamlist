import React from 'react'
import { Link } from 'react-router-dom'

const Header = () => (
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
                        <Link to='/'>Главная</Link>
                    </li>
                    <li>
                        <Link to='team'>Команда</Link>
                    </li>
                </ul>
            </div>
            <a className="hamburger js-navOpenMenu">
                <span></span>
            </a>
        </div>
    </header>
)

export default Header