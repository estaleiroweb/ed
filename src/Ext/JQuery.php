<?php

namespace EstaleiroWeb\ED\Ext;

class JQuery extends Once {
	protected $versions = [
		3 => [
			'<script src="https://code.jquery.com/git/jquery-3.x-git.min.js"></script>',
		],
		'3.6.0' => [
			'<script src="https://code.jquery.com/jquery-3.6.0.min.js" data-hash="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="></script>',
		],
		'3.5.1' => [
			'<script src="https://code.jquery.com/jquery-3.5.1.min.js" data-hash="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="></script>',
		],
		'3.5.0' => [
			'<script src="https://code.jquery.com/jquery-3.5.0.min.js" data-hash="sha256-xNzN2a4ltkB44Mc/Jz3pT4iU1cmeR0FkXs4pru/JxaQ="></script>',
		],
		'3.4.1' => [
			'<script src="https://code.jquery.com/jquery-3.4.1.min.js" data-hash="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="></script>',
		],
		'3.4.0' => [
			'<script src="https://code.jquery.com/jquery-3.4.0.min.js" data-hash="sha256-BJeo0qm959uMBGb65z40ejJYGSgR7REI4+CW1fNKwOg="></script>',
		],
		'3.3.1' => [
			'<script src="https://code.jquery.com/jquery-3.3.1.min.js" data-hash="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="></script>',
		],
		'3.3.0' => [
			'<script src="https://code.jquery.com/jquery-3.3.0.min.js" data-hash="sha256-RTQy8VOmNlT6b2PIRur37p6JEBZUE7o8wPgMvu18MC4="></script>',
		],
		'3.2.1' => [
			'<script src="https://code.jquery.com/jquery-3.2.1.min.js" data-hash="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="></script>',
		],
		'3.2.0' => [
			'<script src="https://code.jquery.com/jquery-3.2.0.min.js" data-hash="sha256-JAW99MJVpJBGcbzEuXk4Az05s/XyDdBomFqNlM3ic+I="></script>',
		],
		'3.1.1' => [
			'<script src="https://code.jquery.com/jquery-3.1.1.min.js" data-hash="sha256-hVVnYaiADRTO2PzUGmuLJr8BLUSjGIZsDYGmIJLv2b8="></script>',
		],
		'3.1.0' => [
			'<script src="https://code.jquery.com/jquery-3.1.0.min.js" data-hash="sha256-cCueBR6CsyA4/9szpPfrX3s49M9vUU5BgtiJj06wt/s="></script>',
		],
		'3.0.0' => [
			'<script src="https://code.jquery.com/jquery-3.0.0.min.js" data-hash="sha256-JmvOoLtYsmqlsWxa7mDSLMwa6dZ9rrIdtrrVYRnDRH0="></script>',
		],
		2 => [
			'<script src="https://code.jquery.com/git/jquery-2.x-git.min.js"></script>',
		],
		'2.2.4' => [
			'<script src="https://code.jquery.com/jquery-2.2.4.min.js" data-hash="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44="></script>',
		],
		'2.2.3' => [
			'<script src="https://code.jquery.com/jquery-2.2.3.min.js" data-hash="sha256-a23g1Nt4dtEYOj7bR+vTu7+T8VP13humZFBJNIYoEJo="></script>',
		],
		'2.2.2' => [
			'<script src="https://code.jquery.com/jquery-2.2.2.min.js" data-hash="sha256-36cp2Co+/62rEAAYHLmRCPIych47CvdM+uTBJwSzWjI="></script>',
		],
		'2.2.1' => [
			'<script src="https://code.jquery.com/jquery-2.2.1.min.js" data-hash="sha256-gvQgAFzTH6trSrAWoH1iPo9Xc96QxSZ3feW6kem+O00="></script>',
		],
		'2.2.0' => [
			'<script src="https://code.jquery.com/jquery-2.2.0.min.js" data-hash="sha256-ihAoc6M/JPfrIiIeayPE9xjin4UWjsx2mjW/rtmxLM4="></script>',
		],
		'2.1.4' => [
			'<script src="https://code.jquery.com/jquery-2.1.4.min.js" data-hash="sha256-8WqyJLuWKRBVhxXIL1jBDD7SDxU936oZkCnxQbWwJVw="></script>',
		],
		'2.1.3' => [
			'<script src="https://code.jquery.com/jquery-2.1.3.min.js" data-hash="sha256-ivk71nXhz9nsyFDoYoGf2sbjrR9ddh+XDkCcfZxjvcM="></script>',
		],
		'2.1.2' => [
			'<script src="https://code.jquery.com/jquery-2.1.2.min.js" data-hash="sha256-YE7BKn1ea9jirCHPr/EaW5NxmkZZGb52+ZaD2UKodXY="></script>',
		],
		'2.1.1' => [
			'<script src="https://code.jquery.com/jquery-2.1.1.min.js" data-hash="sha256-h0cGsrExGgcZtSZ/fRz4AwV+Nn6Urh/3v3jFRQ0w9dQ="></script>',
		],
		'2.1.0' => [
			'<script src="https://code.jquery.com/jquery-2.1.0.min.js" data-hash="sha256-8oQ1OnzE2X9v4gpRVRMb1DWHoPHJilbur1LP9ykQ9H0="></script>',
		],
		'2.0.3' => [
			'<script src="https://code.jquery.com/jquery-2.0.3.min.js" data-hash="sha256-sTy1mJ4I/LAjFCCdEB4RAvPSmRCb3CU7YqodohyeOLo="></script>',
		],
		'2.0.2' => [
			'<script src="https://code.jquery.com/jquery-2.0.2.min.js" data-hash="sha256-TZWGoHXwgqBP1AF4SZxHIBKzUdtMGk0hCQegiR99itk="></script>',
		],
		'2.0.1' => [
			'<script src="https://code.jquery.com/jquery-2.0.1.min.js" data-hash="sha256-JD9u5RNjfbbYl/AbiYYvVPKcLNlKNe2urUMuGzNEIck="></script>',
		],
		'2.0.0' => [
			'<script src="https://code.jquery.com/jquery-2.0.0.min.js" data-hash="sha256-1IKHGl6UjLSIT6CXLqmKgavKBXtr0/jJlaGMEkh+dhw="></script>',
		],
		1 => [
			'<script src="https://code.jquery.com/git/jquery-1.x-git.min.js"></script>',
		],
		'1.12.4' => [
			'<script src="https://code.jquery.com/jquery-1.12.4.min.js" data-hash="sha256-ZosEbRLbNQzLpnKIkEdrPv7lOy9C27hHQ+Xp8a4MxAQ="></script>',
		],
		'1.12.3' => [
			'<script src="https://code.jquery.com/jquery-1.12.3.min.js" data-hash="sha256-aaODHAgvwQW1bFOGXMeX+pC4PZIPsvn2h1sArYOhgXQ="></script>',
		],
		'1.12.2' => [
			'<script src="https://code.jquery.com/jquery-1.12.2.min.js" data-hash="sha256-lZFHibXzMHo3GGeehn1hudTAP3Sc0uKXBXAzHX1sjtk="></script>',
		],
		'1.12.1' => [
			'<script src="https://code.jquery.com/jquery-1.12.1.min.js" data-hash="sha256-I1nTg78tSrZev3kjvfdM5A5Ak/blglGzlaZANLPDl3I="></script>',
		],
		'1.12.0' => [
			'<script src="https://code.jquery.com/jquery-1.12.0.min.js" data-hash="sha256-Xxq2X+KtazgaGuA2cWR1v3jJsuMJUozyIXDB3e793L8="></script>',
		],
		'1.11.3' => [
			'<script src="https://code.jquery.com/jquery-1.11.3.min.js" data-hash="sha256-7LkWEzqTdpEfELxcZZlS6wAx5Ff13zZ83lYO2/ujj7g="></script>',
		],
		'1.11.2' => [
			'<script src="https://code.jquery.com/jquery-1.11.2.min.js" data-hash="sha256-Ls0pXSlb7AYs7evhd+VLnWsZ/AqEHcXBeMZUycz/CcA="></script>',
		],
		'1.11.1' => [
			'<script src="https://code.jquery.com/jquery-1.11.1.min.js" data-hash="sha256-VAvG3sHdS5LqTT+5A/aeq/bZGa/Uj04xKxY8KM/w9EE="></script>',
		],
		'1.11.0' => [
			'<script src="https://code.jquery.com/jquery-1.11.0.min.js" data-hash="sha256-spTpc4lvj4dOkKjrGokIrHkJgNA0xMS98Pw9N7ir9oI="></script>',
		],
		'1.10.2' => [
			'<script src="https://code.jquery.com/jquery-1.10.2.min.js" data-hash="sha256-C6CB9UYIS9UJeqinPHWTHVqh/E1uhG5Twh+Y5qFQmYg="></script>',
		],
		'1.10.1' => [
			'<script src="https://code.jquery.com/jquery-1.10.1.min.js" data-hash="sha256-SDf34fFWX/ZnUozXXEH0AeB+Ip3hvRsjLwp6QNTEb3k="></script>',
		],
		'1.10.0' => [
			'<script src="https://code.jquery.com/jquery-1.10.0.min.js" data-hash="sha256-2+LznWeWgL7AJ1ciaIG5rFP7GKemzzl+K75tRyTByOE="></script>',
		],
		'1.9.1' => [
			'<script src="https://code.jquery.com/jquery-1.9.1.min.js" data-hash="sha256-wS9gmOZBqsqWxgIVgA8Y9WcQOa7PgSIX+rPA0VL2rbQ="></script>',
		],
		'1.9.0' => [
			'<script src="https://code.jquery.com/jquery-1.9.0.min.js" data-hash="sha256-f6DVw/U4x2+HjgEqw5BZf67Kq/5vudRZuRkljnbF344="></script>',
		],
		'1.8.3' => [
			'<script src="https://code.jquery.com/jquery-1.8.3.min.js" data-hash="sha256-YcbK69I5IXQftf/mYD8WY0/KmEDCv1asggHpJk1trM8="></script>',
		],
		'1.8.2' => [
			'<script src="https://code.jquery.com/jquery-1.8.2.min.js" data-hash="sha256-9VTS8JJyxvcUR+v+RTLTsd0ZWbzmafmlzMmeZO9RFyk="></script>',
		],
		'1.8.1' => [
			'<script src="https://code.jquery.com/jquery-1.8.1.min.js" data-hash="sha256-/BhPlt0YeU4gTEEHWgCSO+fo5Wh0QjHXTy/fiSH3jSk="></script>',
		],
		'1.8.0' => [
			'<script src="https://code.jquery.com/jquery-1.8.0.min.js" data-hash="sha256-jFdOCgY5bfpwZLi0YODkqNXQdIxKpm6y5O/fy0baSzE="></script>',
		],
		'1.7.2' => [
			'<script src="https://code.jquery.com/jquery-1.7.2.min.js" data-hash="sha256-R7aNzoy2gFrVs+pNJ6+SokH04ppcEqJ0yFLkNGoFALQ="></script>',
		],
		'1.7.1' => [
			'<script src="https://code.jquery.com/jquery-1.7.1.min.js" data-hash="sha256-iBcUE/x23aI6syuqF7EeT/+JFBxjPs5zeFJEXxumwb0="></script>',
		],
		'1.7.0' => [
			'<script src="https://code.jquery.com/jquery-1.7.0.min.js" data-hash="sha256-/05Jde9AMAT4/o5ZAI23rUf1SxDYTHLrkOco0eyRV84="></script>',
		],
		'1.7.0' => [
			'<script src="https://code.jquery.com/jquery-1.7.min.js" data-hash="sha256-/05Jde9AMAT4/o5ZAI23rUf1SxDYTHLrkOco0eyRV84="></script>',
		],
		'1.6.4' => [
			'<script src="https://code.jquery.com/jquery-1.6.4.min.js" data-hash="sha256-lR1rrjnrFy9XqIvWhvepIc8GD9IfWWSPDSC2qPmPxaU="></script>',
		],
		'1.6.3' => [
			'<script src="https://code.jquery.com/jquery-1.6.3.min.js" data-hash="sha256-0/N3n1ET2m2pV8TYFIEUaicsMa7+DT5LZEFP1ob9l0Q="></script>',
		],
		'1.6.2' => [
			'<script src="https://code.jquery.com/jquery-1.6.2.min.js" data-hash="sha256-0W0HoDU0BfzslffvxQomIbx0Jfml6IlQeDlvsNxGDE8="></script>',
		],
		'1.6.1' => [
			'<script src="https://code.jquery.com/jquery-1.6.1.min.js" data-hash="sha256-x4Q3aWDzFj3HYLwBnnLl/teCA3RaVRDGmZKjnR2P53Y="></script>',
		],
		'1.6.0' => [
			'<script src="https://code.jquery.com/jquery-1.6.min.js" data-hash="sha256-5Y2lizFMze76PEhltLiqMVPokNeQTgRINIHY//LCfqo="></script>',
		],
		'1.5.2' => [
			'<script src="https://code.jquery.com/jquery-1.5.2.min.js" data-hash="sha256-jwoZ7oxgazWhCQSVHgon2hiW6v4zxuiMt7y+RV8Foko="></script>',
		],
		'1.5.1' => [
			'<script src="https://code.jquery.com/jquery-1.5.1.min.js" data-hash="sha256-dkuenzrThqqlzerpNoNTmU3mHAvt4IfI9+NXnLRD3js="></script>',
		],
		'1.5.0' => [
			'<script src="https://code.jquery.com/jquery-1.5.min.js" data-hash="sha256-IpJ49qnBwn/FW+xQ8GVI/mTCYp9Z9GLVDKwo5lu5OoM="></script>',
		],
		'1.4.4' => [
			'<script src="https://code.jquery.com/jquery-1.4.4.min.js" data-hash="sha256-UXNk8tRRYvtQN0N7W2y5U9ANmys7ebqH2f5X6m7mBww="></script>',
		],
		'1.4.3' => [
			'<script src="https://code.jquery.com/jquery-1.4.3.min.js" data-hash="sha256-+ACzmeXHpSVPxmu0BxF/44294FKHgOaMn3yH0pn4SGo="></script>',
		],
		'1.4.2' => [
			'<script src="https://code.jquery.com/jquery-1.4.2.min.js" data-hash="sha256-4joqTi18K0Hrzdj/wGed9xQOt/UuHuur+CeogYJkPFk="></script>',
		],
		'1.4.1' => [
			'<script src="https://code.jquery.com/jquery-1.4.1.min.js" data-hash="sha256-LOx49zn73f7YUs15NNJTDnzEyPFLOGc7A7pfuICtTMc="></script>',
		],
		'1.4.0' => [
			'<script src="https://code.jquery.com/jquery-1.4.min.js" data-hash="sha256-iauvHiRxsAUlsGlASOF5wPOaJnTjvLNEYOprxIAYgr4="></script>',
		],
		'1.3.2' => [
			'<script src="https://code.jquery.com/jquery-1.3.2.min.js" data-hash="sha256-yDcKLQUDWenVBazEEeb0V6SbITYKIebLySKbrTp2eJk="></script>',
		],
		'1.3.1' => [
			'<script src="https://code.jquery.com/jquery-1.3.1.min.js" data-hash="sha256-F+wfFu+siTub2Ju6XxPLHgv5OL3Jzs5srj7Xfxj6b9c="></script>',
		],
		'1.3.0' => [
			'<script src="https://code.jquery.com/jquery-1.3.min.js" data-hash="sha256-kAGRpEMRXYtIqdaNMGLos9cSlyeVG4YXRltIW68lMAY="></script>',
		],
		'1.2.6' => [
			'<script src="https://code.jquery.com/jquery-1.2.6.min.js" data-hash="sha256-1UhTB3WmKG9JumbgcVh2tOxZhZZrApHCFWj+z8QXjo0="></script>',
		],
		'1.2.5' => [
			'<script src="https://code.jquery.com/jquery-1.2.5.min.js" data-hash="sha256-26PtLoW+gskQlBnRX5SOrzgy//zgk3bYZl4pEFwo6cY="></script>',
		],
		'1.2.4' => [
			'<script src="https://code.jquery.com/jquery-1.2.4.min.js" data-hash="sha256-mfPAEMp15RaTF6QxFReOn5ax5KwxRw5VCEN9TntGdHo="></script>',
		],
		'1.2.3' => [
			'<script src="https://code.jquery.com/jquery-1.2.3.min.js" data-hash="sha256-8cSgp7XerSMfybQvBpZaA2q3oqeIdohH64HhUo1kAq0="></script>',
		],
		'1.2.2' => [
			'<script src="https://code.jquery.com/jquery-1.2.2.min.js" data-hash="sha256-09D/HFXvOsiqH76j5h1VDzlQpnKeA/y/wcPvFSQbqE4="></script>',
		],
		'1.2.1' => [
			'<script src="https://code.jquery.com/jquery-1.2.1.min.js" data-hash="sha256-GKsQaBS2JRBXx7c52Bi0OIe0Q8Qrj0iKBSruqkzqax8="></script>',
		],
		'1.2.0' => [
			'<script src="https://code.jquery.com/jquery-1.2.min.js" data-hash="sha256-EA4aFzphEyGP+0nhOhR3j6O5H/f82frFxSO67bDxt/s="></script>',
		],
		'1.1.4' => [
			'<script src="https://code.jquery.com/jquery-1.1.4.pack.js" data-hash="sha256-+tQiZSEnx9ZWVnf3nZTkQJWN2dOdm1/R4Mq2DFHmGCU="></script>',
		],
		'1.1.3' => [
			'<script src="https://code.jquery.com/jquery-1.1.3.pack.js" data-hash="sha256-10nD2u5qpbofah85bQUTmT1eMFz2FHLDDUo4iVkCS8I="></script>',
		],
		'1.1.2' => [
			'<script src="https://code.jquery.com/jquery-1.1.2.pack.js" data-hash="sha256-tDnFvsvtmqQW7ZS6LCqPCN7EW2axCSpImmDbIbqOO1Q="></script>',
		],
		'1.1.1' => [
			'<script src="https://code.jquery.com/jquery-1.1.1.pack.js" data-hash="sha256-aIROcR0yayONO43ZZgadzr177fNOhHlg/FqPXP6qfhI="></script>',
		],
		'1.1.pack' => [
			'<script src="https://code.jquery.com/jquery-1.1.pack.js" data-hash="sha256-45/3y0ZBU5mbxdSfYDFjfDz2b12X1JxeWN4d1Sb88I0="></script>',
		],
		'1.0.4' => [
			'<script src="https://code.jquery.com/jquery-1.0.4.pack.js" data-hash="sha256-upOUW/O9T857epnazR8IlX1C0ZSEE19MuqWoC75KUOE="></script>',
		],
		'1.0.3' => [
			'<script src="https://code.jquery.com/jquery-1.0.3.pack.js" data-hash="sha256-C3irtOlBrTx9D2/8u15nc1EvZnMtOoBwM6EdfRnrX54="></script>',
		],
		'1.0.2' => [
			'<script src="https://code.jquery.com/jquery-1.0.2.pack.js" data-hash="sha256-pc3FU00yv6kziM+OaF0SZCb9C8Vnu+/yBoN6cv/d+4o="></script>',
		],
		'1.0.1' => [
			'<script src="https://code.jquery.com/jquery-1.0.1.pack.js" data-hash="sha256-SrsYPU1j+vmvkCrKCAH5P9jT4qTFSojD8UX725XytsY="></script>',
		],
		'1.0.0' => [
			'<script src="https://code.jquery.com/jquery-1.0.pack.js" data-hash="sha256-IOSKIArzLq73GsbYQXAdgVp3VoqDhEzco1c83eaAmx8="></script>',
		],
	];
}
