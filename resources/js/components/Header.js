import React, {Component} from 'react'
import {Link} from 'react-router-dom'
import {withTranslation} from 'react-i18next'
import {getCurrentUser} from '../api/Api'
import data from '../data/data.json';

const handleLogout = () => {
    axios.post('/logout')
        .then(() => location.href = '/')
};

class Header extends Component {

	constructor (props) {
		super(props)
		this.state = {
			currentUser: [],
		}
	}

	componentDidMount () {
		getCurrentUser().then(response => {
			this.setState({
				currentUser: response,
			});
		})
	}

    render() {
		let currentUser = this.state.currentUser;
        if(currentUser && currentUser.roles) {
        }
        const { t } = this.props;
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
                                <Link to='home'>{t(data.menu.home)}</Link>
                            </li>
                            <li>
                                <Link to='/'>{t(data.menu.team)}</Link>
                            </li>
                            <li>
                                <Link to='/logout' onClick={handleLogout}>{t(data.menu.logout)}</Link>
                            </li>
		                        <li>
			                        <a onClick={() => window.location.href="/admin"} >{t(data.menu.admin)}</a>
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
export default withTranslation()(Header)
