import SlidingAuth from './SlidingAuth';

/** Legacy entry; controllers render Auth/SlidingAuth with mode. */
export default function Login() {
    return <SlidingAuth mode="login" />;
}
