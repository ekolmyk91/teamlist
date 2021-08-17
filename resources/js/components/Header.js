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

        const renderAuthButton = () => {
            let currentUser = this.state.currentUser,
                isLoggedIn = false;
    
            if (currentUser.length != 0) {
                if (currentUser.roles[0].name == 'admin') {
                    isLoggedIn = true;
                }
            }
            if (isLoggedIn) {
              return  <li><a onClick={() => window.location.href="/admin"} >{t(data.menu.admin)}</a></li>;
            }
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
                                <Link to='/'>{t(data.menu.home)}</Link>
                            </li>
                            <li>
                                <Link to='/team'>{t(data.menu.team)}</Link>
                            </li>
                            <li>
                                <Link to='/logout' onClick={handleLogout}>{t(data.menu.logout)}</Link>
                            </li>
                            {renderAuthButton()}
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
