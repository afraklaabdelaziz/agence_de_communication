import classNames from 'classnames';
import './styles.scss';

export const SavePaymentMethod = ({label, onChange, checked}) => {
    return (
        <div className='wc-stripe-save-payment-method'>
            <label>
                <input type='checkbox' onChange={(e) => onChange(e.target.checked)}/>
                <svg
                    className={classNames('wc-stripe-components-checkbox__mark', {checked: checked})}
                    aria-hidden="true"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 20">
                    <path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"/>
                </svg>
            </label>
            <span>{label}</span>
        </div>
    )
}